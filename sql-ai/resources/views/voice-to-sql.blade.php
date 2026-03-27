<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL AI Voice Assistant </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ENABLE DARK MODE -->
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(to bottom right, #f1f5f9, #eef2ff);
        }

        .dark body {
            background: linear-gradient(to bottom right, #0f172a, #1e293b);
        }

        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .dark .card {
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mic-btn {
            transition: all 0.3s ease;
        }

        .mic-btn:hover {
            transform: translateY(-2px) scale(1.03);
        }

        .mic-btn.recording {
            animation: pulse 1.5s infinite;
            background-color: #ef4444 !important;
            color: white;
        }

        @keyframes pulse {
            0%,100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        .fade-in {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="min-h-screen py-12 transition-colors duration-300">

    <div class="max-w-4xl mx-auto px-4">

        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-3">
                SQL AI Voice Assistant
            </h1>
            <p class="text-gray-600 dark:text-gray-300 text-lg">Speak naturally or upload a voice file</p>
        </div>

        <!-- Input Card -->
        <div class="card p-10 fade-in">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-8 text-center">
                How do you want to ask?
            </h2>

            <form action="{{ route('voice-to-sql') }}" method="POST" enctype="multipart/form-data" id="voiceForm">
                @csrf

                <div class="mb-12 text-center">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-5">
                        🎤 Speak Your Query (Recommended)
                    </label>

                    <div class="flex flex-col items-center gap-5">
                        <button type="button" id="recordBtn"
                            class="mic-btn w-28 h-28 flex items-center justify-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-3xl text-5xl shadow-2xl">
                            <i class="fas fa-microphone"></i>
                        </button>

                        <p id="recordingStatus" class="text-sm font-medium text-red-500 hidden">
                            🔴 Recording...
                        </p>
                    </div>
                </div>

                <!-- Divider -->
                <div class="relative my-10">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white dark:bg-gray-800 px-5 text-sm text-gray-400">OR</span>
                    </div>
                </div>

                <!-- Upload -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        📁 Upload MP3 or WAV File
                    </label>

                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-6 text-center">
                        <input type="file" name="audio_file" id="audioFile" accept="audio/*"
                            class="w-full text-sm text-gray-500 dark:text-gray-300">
                    </div>
                </div>

                <button type="submit" id="submitBtn"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold py-5 rounded-2xl">
                    Send to AI
                </button>
            </form>
        </div>

        <!-- RESULT -->
        @if(session('result'))
        <div class="card p-10 mt-10 fade-in">

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-6">Result</h2>

            <p class="text-gray-600 dark:text-gray-300">
                "{{ session('result')['user_question'] }}"
            </p>

            <pre class="bg-gray-900 text-green-400 p-5 rounded-xl mt-4">
{{ session('result')['generated_sql'] }}
            </pre>

        </div>
        @endif
    </div>

    <!-- 🔥 THEME BUTTON -->
    <button id="themeToggle"
        class="fixed bottom-6 left-6 z-[9999] bg-black text-white dark:bg-yellow-400 dark:text-black p-4 rounded-full shadow-xl">
        🌙
    </button>

    <!-- JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ===== THEME FIX =====
            const html = document.documentElement;
            const toggleBtn = document.getElementById('themeToggle');

            // load saved theme
            if (localStorage.getItem('theme') === 'dark') {
                html.classList.add('dark');
                toggleBtn.innerHTML = '☀️';
            }

            toggleBtn.addEventListener('click', () => {
                html.classList.toggle('dark');

                if (html.classList.contains('dark')) {
                    localStorage.setItem('theme', 'dark');
                    toggleBtn.innerHTML = '☀️';
                } else {
                    localStorage.setItem('theme', 'light');
                    toggleBtn.innerHTML = '🌙';
                }
            });

            // ===== YOUR ORIGINAL JS (UNCHANGED) =====
            const recordBtn = document.getElementById('recordBtn');
            const recordingStatus = document.getElementById('recordingStatus');
            const submitBtn = document.getElementById('submitBtn');
            const voiceForm = document.getElementById('voiceForm');
            const audioFileInput = document.getElementById('audioFile');

            let mediaRecorder;
            let audioChunks = [];
            let isRecording = false;

            if (!recordBtn) return;

            recordBtn.addEventListener('click', async () => {
                if (!isRecording) {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];

                        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);

                        mediaRecorder.onstop = () => {
                            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                            const file = new File([audioBlob], "voice-query.webm", { type: "audio/webm" });

                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            audioFileInput.files = dataTransfer.files;

                            recordingStatus.classList.add('hidden');
                            recordBtn.innerHTML = '✅';
                        };

                        mediaRecorder.start();
                        isRecording = true;
                        recordingStatus.classList.remove('hidden');

                    } catch (err) {
                        alert("Microphone access denied.");
                    }
                } else {
                    if (mediaRecorder) mediaRecorder.stop();
                    isRecording = false;
                }
            });

            voiceForm.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Processing...';
            });
        });
    </script>

</body>
</html>