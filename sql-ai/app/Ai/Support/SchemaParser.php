<?php
namespace App\Ai\Support;

class SchemaParser
{
    public static function parse(string $rawSql): array
    {
        $tables = [];

        preg_match_all(
            '/CREATE\s+TABLE\s+`?(\w+)`?\s*\((.+?)\)\s*(?:ENGINE|;)/is',
            $rawSql,
            $tableMatches,
            PREG_SET_ORDER
        );

        foreach ($tableMatches as $tableMatch) {
            $tableName = $tableMatch[1];
            $body      = $tableMatch[2];

            $tables[$tableName] = [
                'columns'      => self::parseColumns($body),
                'primary_key'  => self::parsePrimaryKey($body),
                'foreign_keys' => self::parseForeignKeys($body, $tableName),
                'unique_keys'  => self::parseUniqueKeys($body),
                'indexes'      => self::parseIndexes($body),
            ];
        }

        return $tables;
    }

    private static function parseColumns(string $body): array
    {
        $columns = [];
        $lines   = explode("\n", $body);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip constraint lines
            if (preg_match('/^(PRIMARY|UNIQUE|KEY|INDEX|CONSTRAINT|FOREIGN|CHECK)/i', $line)) {
                continue;
            }

            // Match column definition: `col_name` TYPE ...
            if (preg_match('/^`?(\w+)`?\s+(\w+(?:\([^)]+\))?)\s*(.*)/i', $line, $m)) {
                $colName = $m[1];
                $colType = $m[2];
                $rest    = strtoupper($m[3]);

                $columns[$colName] = [
                    'type'     => strtoupper($colType),
                    'nullable' => !str_contains($rest, 'NOT NULL'),
                    'default'  => self::extractDefault($rest),
                    'auto_inc' => str_contains($rest, 'AUTO_INCREMENT'),
                ];
            }
        }

        return $columns;
    }

    private static function parsePrimaryKey(string $body): array
    {
        preg_match('/PRIMARY\s+KEY\s*\(`?(.+?)`?\)/i', $body, $m);
        if (empty($m[1])) return [];
        return array_map(
            fn($c) => trim($c, '` '),
            explode(',', $m[1])
        );
    }

    private static function parseForeignKeys(string $body, string $tableName): array
    {
        $fks = [];
        preg_match_all(
            '/FOREIGN\s+KEY\s*\(`?(\w+)`?\)\s*REFERENCES\s*`?(\w+)`?\s*\(`?(\w+)`?\)/i',
            $body,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $fks[] = [
                'column'            => $m[1],
                'references_table'  => $m[2],
                'references_column' => $m[3],
            ];
        }

        return $fks;
    }

    private static function parseUniqueKeys(string $body): array
    {
        $unique = [];
        preg_match_all('/UNIQUE\s+(?:KEY|INDEX)\s*`?\w*`?\s*\((.+?)\)/i', $body, $matches);

        foreach ($matches[1] as $cols) {
            $unique[] = array_map(fn($c) => trim($c, '` '), explode(',', $cols));
        }

        return $unique;
    }

    private static function parseIndexes(string $body): array
    {
        $indexes = [];
        preg_match_all('/(?<!UNIQUE\s)(?<!PRIMARY\s)KEY\s+`?(\w+)`?\s*\((.+?)\)/i', $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $indexes[$m[1]] = array_map(fn($c) => trim($c, '` '), explode(',', $m[2]));
        }

        return $indexes;
    }

    private static function extractDefault(string $rest): ?string
    {
        if (preg_match("/DEFAULT\s+'?([^'\s,]+)'?/i", $rest, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Build compact schema summary for the AI prompt.
     * Includes relationships — critical for JOIN accuracy.
     */
    public static function toCompactSummary(array $parsed): string
    {
        $lines = [];

        foreach ($parsed as $table => $meta) {
            $cols = [];
            foreach ($meta['columns'] as $colName => $colMeta) {
                $pk   = in_array($colName, $meta['primary_key']) ? ' PK' : '';
                $null = $colMeta['nullable'] ? '' : ' NOT NULL';
                $ai   = $colMeta['auto_inc'] ? ' AI' : '';
                $cols[] = "`{$colName}` {$colMeta['type']}{$pk}{$ai}{$null}";
            }

            $lines[] = "TABLE `{$table}` (\n  " . implode(",\n  ", $cols) . "\n)";

            // Append FK relationships
            foreach ($meta['foreign_keys'] as $fk) {
                $lines[] = "  -- `{$table}`.`{$fk['column']}` → `{$fk['references_table']}`.`{$fk['references_column']}`";
            }

            // Append unique constraints
            foreach ($meta['unique_keys'] as $uk) {
                $lines[] = "  -- UNIQUE (" . implode(', ', array_map(fn($c) => "`{$c}`", $uk)) . ")";
            }
        }

        return implode("\n\n", $lines);
    }

    /**
     * Validate SQL against parsed schema.
     * Now allows all query types (SELECT, INSERT, UPDATE, DELETE).
     */
    public static function validateSql(string $sql, array $parsed): array
    {
        $errors     = [];
        $upperSql   = strtoupper(trim($sql));
        $tableNames = array_keys($parsed);

        // Extract all table references (FROM, JOIN, INTO, UPDATE)
        preg_match_all(
            '/(?:FROM|JOIN|INTO|UPDATE)\s+`?(\w+)`?/i',
            $sql,
            $tableMatches
        );
        $usedTables = array_unique($tableMatches[1]);

        foreach ($usedTables as $table) {
            if (!isset($parsed[$table])) {
                $errors[] = "Table `{$table}` does not exist in the schema.";
                continue;
            }

            // Check qualified column references (table.column or `table`.`column`)
            preg_match_all('/`?' . preg_quote($table, '/') . '`?\.`?(\w+)`?/i', $sql, $colMatches);
            foreach (array_unique($colMatches[1]) as $col) {
                if (!isset($parsed[$table]['columns'][$col])) {
                    $errors[] = "Column `{$table}`.`{$col}` does not exist.";
                }
            }
        }

        return $errors;
    }
}