<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kapil Agarwal - Senior Software & AI Engineer. Scalable microservices and LLM-powered developer tools for 1M+ users. Bengaluru, India.">
    <title>KapilLabs · Kapil Agarwal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>

<body>

    {{-- STATUS BAR --}}
    <div class="status-bar">
        <div class="status-left">
            <span class="status-dot"></span>
            <span class="status-text mono">KAPILLABS · SYSTEM OPERATIONAL</span>
        </div>
        <div class="status-right">
            <span class="mono">BENGALURU · IN</span>
            <span class="status-sep">·</span>
            <span class="mono" id="statusTime"></span>
            <span class="status-sep">·</span>
            <span class="mono">v2026</span>
        </div>
    </div>

    {{-- NAV --}}
    <nav class="nav" id="mainNav">
        <a href="/" class="nav-logo">KAPIL<span>LABS</span></a>
        <ul class="nav-links">
            <li><a href="#work">work</a></li>
            <li><a href="#experience">experience</a></li>
            <li><a href="/ai/">ai_tools/</a></li>
            <li><a href="#about">about</a></li>
            <li><a href="#contact" class="nav-cta">get_in_touch -></a></li>
        </ul>
        <button class="nav-hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <div class="nav-drawer" id="navDrawer">
        <a href="#work" class="drawer-link">work</a>
        <a href="#experience" class="drawer-link">experience</a>
        <a href="/ai/" class="drawer-link">ai_tools/</a>
        <a href="#about" class="drawer-link">about</a>
        <a href="#contact" class="drawer-link drawer-cta">get_in_touch -></a>
    </div>

    {{-- HERO --}}
    <section id="hero">
        <div class="hero-scanlines" aria-hidden="true"></div>
        <div class="hero-inner">
            <div class="hero-eyebrow">
                <span class="mono">SENIOR SOFTWARE &amp; AI ENGINEER</span>
                <span class="hero-sep">·</span>
                <span class="mono dim">ENGINE v2026.1</span>
            </div>
            <div class="hero-names">
                <h1 class="hero-n1">Kapil</h1>
                <h1 class="hero-n2">Agarwal</h1>
            </div>
            <div class="hero-foot">
                <div class="hero-tagline">
                    <p class="mono">Built from first principles.</p>
                    <p class="mono">Bound by engineering contracts.</p>
                    <p class="mono accent">Speed is the product.</p>
                </div>
                <div class="hero-kws">
                    <span class="kw active">Engineer</span>
                    <span class="kw">Builder</span>
                    <span class="kw">Architect</span>
                    <span class="kw">Maker</span>
                </div>
                <div class="hero-btns">
                    <a href="#work" class="btn-primary">Explore Work</a>
                    <a href="/ai/" class="btn-ghost">AI Tools -></a>
                </div>
            </div>
        </div>
        <div class="hero-strip">
            <div class="strip-item">
                <div class="sk mono">experience</div>
                <div class="sv">5+ yrs</div>
            </div>
            <div class="strip-item">
                <div class="sk mono">users_served</div>
                <div class="sv">1M+</div>
            </div>
            <div class="strip-item">
                <div class="sk mono">bugs_reduced</div>
                <div class="sv">50% ↓</div>
            </div>
            <div class="strip-item">
                <div class="sk mono">ai_tools</div>
                <div class="sv">4 live</div>
            </div>
            <div class="strip-item">
                <div class="sk mono">core_stack</div>
                <div class="sv">Kafka · LLM · AWS</div>
            </div>
        </div>
    </section>

    {{-- MARQUEE --}}
    <div class="marquee-wrap" aria-hidden="true">
        <div class="marquee-track">
            <span>FastAPI</span><span class="mdot">✦</span><span>Apache Kafka</span><span class="mdot">✦</span><span>LangChain</span><span class="mdot">✦</span><span>ChromaDB</span><span class="mdot">✦</span><span>AWS ECS</span><span class="mdot">✦</span><span>Docker</span><span class="mdot">✦</span><span>Laravel</span><span class="mdot">✦</span><span>RAG · Embeddings</span><span class="mdot">✦</span><span>OpenAI · Anthropic</span><span class="mdot">✦</span><span>MySQL · Redis</span><span class="mdot">✦</span>
            <span>FastAPI</span><span class="mdot">✦</span><span>Apache Kafka</span><span class="mdot">✦</span><span>LangChain</span><span class="mdot">✦</span><span>ChromaDB</span><span class="mdot">✦</span><span>AWS ECS</span><span class="mdot">✦</span><span>Docker</span><span class="mdot">✦</span><span>Laravel</span><span class="mdot">✦</span><span>RAG · Embeddings</span><span class="mdot">✦</span><span>OpenAI · Anthropic</span><span class="mdot">✦</span><span>MySQL · Redis</span><span class="mdot">✦</span>
        </div>
    </div>

    {{-- STATEMENT --}}
    <section class="sec" id="statement">
        <div class="sec-hd"><span class="sec-num mono">01 / STATEMENT</span></div>
        <h2 class="stmt-h fade-up">5+ years making<br>systems that <em>scale,</em><br>think &amp; ship.</h2>
        <p class="stmt-sub mono fade-up">// From event-driven microservices on Kafka to LLM-powered developer tools<br>// I build things that hold up at 1M+ users.</p>
    </section>

    {{-- EXPERIENCE --}}
    <section class="sec" id="experience">
        <div class="sec-hd"><span class="sec-num mono">02 / EXPERIENCE</span></div>
        <div class="exp-list">

            <div class="exp-item fade-up">
                <div class="exp-l">
                    <div class="exp-dates mono">Sep 2022 - Present</div>
                    <div class="exp-co">AltInvest</div>
                    <div class="exp-loc mono">formerly PropertyShare · BLR</div>
                    <span class="exp-badge">Current</span>
                </div>
                <div class="exp-r">
                    <div class="exp-role">Software Engineer</div>
                    <ul class="exp-buls">
                        <li>Event-driven microservices on <strong>Kafka</strong> - async comms handling <mark>1M+ users</mark>, eliminating tight coupling</li>
                        <li>Refactored legacy systems with Strategy &amp; Factory patterns - reduced production bugs by <mark>50%</mark></li>
                        <li>Abstract Factory integration framework - cut third-party onboarding by <mark>40%</mark></li>
                        <li>Containerised with Docker; CI/CD on AWS - <mark>25%</mark> reduction in API response time</li>
                    </ul>
                </div>
            </div>

            <div class="exp-item fade-up">
                <div class="exp-l">
                    <div class="exp-dates mono">Oct 2020 - Aug 2022</div>
                    <div class="exp-co">Mantra Labs</div>
                    <div class="exp-loc mono">Care Health Insurance · BLR</div>
                </div>
                <div class="exp-r">
                    <div class="exp-role">Software Engineer</div>
                    <ul class="exp-buls">
                        <li>Integrated WhatsApp, payment gateways &amp; dialer systems - reduced manual effort by <mark>35%</mark></li>
                        <li>DB query optimisation &amp; indexing - cut data processing time by <mark>40%</mark></li>
                        <li>Designed <strong>RBAC</strong> and dynamic dashboards with fine-grained access control</li>
                        <li>Established code review culture - reduced production defects by <mark>20%</mark></li>
                    </ul>
                </div>
            </div>

            <div class="exp-item fade-up">
                <div class="exp-l">
                    <div class="exp-dates mono">Dec 2019 - Jan 2020</div>
                    <div class="exp-co">AppTech Interactive</div>
                    <div class="exp-loc mono">Gwalior</div>
                </div>
                <div class="exp-r">
                    <div class="exp-role">Android Developer Intern</div>
                    <ul class="exp-buls">
                        <li>Built streaming, blogging &amp; EdTech Android apps with Firebase and Google/Meta Ads integration</li>
                    </ul>
                </div>
            </div>

        </div>
    </section>

    {{-- PROJECTS --}}
    <section class="sec" id="work">

        <div class="sec-hd">
            <span class="sec-num mono">03 / PROJECTS</span>
            <a href="https://github.com/while1-KAPIL-do"
                target="_blank"
                rel="noopener"
                class="sec-link mono">
                github ↗
            </a>
        </div>

        <div class="proj-list">

            <!-- PROJECT 01 -->
            <a class="proj-item fade-up">
                <span class="proj-num mono">01</span>
                <div class="proj-info">
                    <div class="proj-name">LLDForge</div>
                    <div class="proj-desc mono">
                        PRD → production-ready low level design generation using AI agents,
                        vector memory and architecture-aware pipelines.
                    </div>
                </div>
                <div class="proj-tags">
                    <span class="ptag inprogress">BUILDING</span>
                    <span class="ptag cat">AI SYSTEMS</span>
                    <span class="ptag soon">2026</span>
                </div>
                <!-- <span class="proj-arr">↗</span> -->
            </a>

            <!-- PROJECT 02 -->
            <a class="proj-item fade-up" href="/ai/token-dashboard">
                <span class="proj-num mono">02</span>
                <div class="proj-info">
                    <div class="proj-name">LLM Token Dashboard</div>
                    <div class="proj-desc mono">
                        Real-time observability for multi-model AI workloads —
                        token usage, latency and cost analytics in one place.
                    </div>
                </div>
                <div class="proj-tags">
                    <span class="ptag live">LIVE ✦</span>
                    <span class="ptag cat">OBSERVABILITY</span>
                    <span class="ptag soon">2026</span>
                </div>
                <span class="proj-arr">↗</span>
            </a>

            <!-- PROJECT 03 -->
            <a class="proj-item fade-up" href="/ai/sql-assistant">
                <span class="proj-num mono">03</span>
                <div class="proj-info">
                    <div class="proj-name">SQL Voice Assistant</div>
                    <div class="proj-desc mono">
                        Natural language → optimized SQL queries using voice-driven AI workflows.
                    </div>
                </div>
                <div class="proj-tags">
                    <span class="ptag live">LIVE ✦</span>
                    <span class="ptag cat">VOICE AI</span>
                    <span class="ptag soon">2026</span>
                </div>
                <span class="proj-arr">↗</span>
            </a>

        </div>
    </section>

    {{-- VIDEO - WORKSPACE --}}
    <div class="vid-wrap">
        <video autoplay muted loop playsinline preload="auto">
            <source src="https://juanmora.co/videos-work/desk_jm3.mp4" type="video/mp4">
        </video>

        <div class="vid-overlay">
            <span class="sec-num mono">// SYSTEM.STATUS = FOCUSED</span>
            <p class="ai-h">
                While the world sleeps,<br>
                <em>
                    we build<span class="blink-cursor">_</span>
                </em>
            </p>
        </div>
    </div>


    {{-- AI TOOLS --}}
    <section class="ai-sec" id="ai-tools">
        <div class="ai-inner">
            <div class="ai-l fade-up">
                <div class="sec-num mono" style="color:#2a2a2a;margin-bottom:18px">04 / AI_TOOLS</div>
                <h2 class="ai-h">Tools I built<br>that <em>actually work</em></h2>
                <p class="ai-sub mono">// Live AI-powered tools at kapillabs.com/ai/<br>// Built with LangChain · FastAPI · OpenAI · Anthropic</p>
                <a href="/ai/" class="ai-cta mono">explore_all_tools() -></a>
            </div>
            <div class="ai-r fade-up">

                <a href="/ai/sql-assistant" class="ai-card">
                    <div>
                        <div class="ai-card-name">LLM Token Dashboard</div>
                        <div class="ai-card-desc mono">Real-time multi-provider cost tracking</div>
                    </div>
                    <!-- <span class="abadge soon-b">SOON</span> -->
                    <span class="live-dot"></span>
                </a>

                <a href="/ai/token-dashboard" class="ai-card">
                    <div>
                        <div class="ai-card-name">SQL Voice Assistant</div>
                        <div class="ai-card-desc mono">Voice -> natural language -> SQL queries</div>
                    </div>
                    <span class="live-dot"></span>
                </a>
                <!-- <div class="ai-card">
                    <div>
                        <div class="ai-card-name">LLDForge</div>
                        <div class="ai-card-desc mono">PRD document -> Low-Level Design artifacts</div>
                    </div>
                    <span class="abadge build-b">BUILDING</span>
                </div> -->
            </div>
        </div>
    </section>

    {{-- ABOUT --}}
    <section class="sec" id="about">
        <div class="sec-hd"><span class="sec-num mono">05 / ABOUT</span></div>
        <div class="about-grid">
            <div class="about-l fade-up">
                <h2 class="about-h">Good engineering<br><em>takes taste.</em><br>I have both.</h2>
                <p class="about-p">Backend engineer with 5+ years shipping scalable microservices, event-driven systems, and AI tooling. Deep hands-on with Kafka, FastAPI, Laravel, Docker, and AWS. Currently building LLM-powered developer workflow tools.</p>
                <p class="about-p">B.E. Computer Science - SRCM Gwalior, RGPV University, 2020.</p>
                <p class="about-p">Beyond code: Piano · Strength Training · Running · Pickleball · Audiobooks.</p>
                <a href="mailto:kapillabs.work@gmail.com" class="about-link mono">get_in_touch() -></a>
            </div>
            <div class="about-r fade-up">
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-n">5<span>+</span></div>
                        <div class="stat-l mono">years_production</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-n">1M<span>+</span></div>
                        <div class="stat-l mono">users_served</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-n">50<span>%</span></div>
                        <div class="stat-l mono">bugs_reduced</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-n">40<span>%</span></div>
                        <div class="stat-l mono">faster_onboarding</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SKILLS --}}
    <section class="sec" id="skills">
        <div class="sec-hd"><span class="sec-num mono">06 / STACK</span></div>
        <div class="skills-wrap">
            <div class="sg fade-up">
                <div class="sg-title mono">// backend</div>
                <div class="sg-list">
                    <span>FastAPI</span>
                    <span>Laravel</span>
                    <span>REST APIs</span>
                    <span>Microservices</span>
                </div>
            </div>
            <div class="sg fade-up">
                <div class="sg-title mono accent">// ai · llm</div>
                <div class="sg-list accent-list">
                    <!-- <span>LangChain</span> -->
                    <span>RAG</span>
                    <span>Vector Search</span>
                    <!-- <span>ChromaDB</span> -->
                    <span>Embeddings</span>
                </div>
            </div>
            <div class="sg fade-up">
                <div class="sg-title mono">// streaming</div>
                <div class="sg-list">
                    <span>Apache Kafka</span>
                    <span>Event-Driven Arch</span>
                </div>
            </div>
            <div class="sg fade-up">
                <div class="sg-title mono accent">// cloud · devops</div>
                <div class="sg-list accent-list">
                    <span>AWS ECS</span>
                    <span>AWS S3</span>
                    <span>Docker</span>
                    <span>CI/CD</span>
                </div>
            </div>
            <div class="sg fade-up">
                <div class="sg-title mono">// data · tools</div>
                <div class="sg-list">
                    <span>MySQL</span>
                    <span>Redis</span>
                    <span>Git</span>
                    <span>Postman</span>
                    <span>JIRA</span>
                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer id="contact">
        <div class="footer-top">
            <div class="footer-nl">KAPIL</div>
            <div class="footer-c">
                <div class="footer-label mono">// want to build something together?</div>
                <a href="mailto:kapillabs.work@gmail.com" class="footer-email">kapillabs.work@gmail.com</a>
                <div class="footer-socials">
                    <a href="https://github.com/while1-KAPIL-do" target="_blank" rel="noopener" class="mono">GitHub ↗</a>
                    <a href="https://www.linkedin.com/in/kapil-a-17295a147" target="_blank" rel="noopener" class="mono">LinkedIn ↗</a>
                    <a href="mailto:kapillabs.work@gmail.com" class="mono">Email ↗</a>
                </div>
            </div>
            <div class="footer-nr">LABS</div>
        </div>
        <div class="footer-bot">
            <span class="mono footer-copy">© KapilLabs 2026 · Bengaluru, India</span>
            <!-- <div class="footer-stack-tags">
                <span class="mono">Laravel</span><span class="mono">Blade</span><span class="mono">Space Grotesk</span>
            </div> -->
            <div class="footer-links">
                <a href="https://github.com/while1-KAPIL-do" target="_blank" class="mono">GH</a>
                <a href="https://www.linkedin.com/in/kapil-a-17295a147" target="_blank" class="mono">LI</a>
                <a href="mailto:kapillabs.work@gmail.com" class="mono">EM</a>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            /* Live clock */
            function tick() {
                var el = document.getElementById('statusTime');
                if (!el) return;
                var n = new Date(),
                    h = String(n.getHours()).padStart(2, '0'),
                    m = String(n.getMinutes()).padStart(2, '0'),
                    s = String(n.getSeconds()).padStart(2, '0');
                el.textContent = h + ':' + m + ':' + s + ' IST';
            }
            tick();
            setInterval(tick, 1000);

            /* Hamburger */
            var hb = document.getElementById('hamburger'),
                dr = document.getElementById('navDrawer');
            hb.addEventListener('click', function() {
                dr.classList.toggle('open');
                hb.classList.toggle('open');
            });
            document.querySelectorAll('.drawer-link').forEach(function(l) {
                l.addEventListener('click', function() {
                    dr.classList.remove('open');
                    hb.classList.remove('open');
                });
            });

            /* Keyword cycle */
            var kws = document.querySelectorAll('.kw'),
                ki = 0;
            setInterval(function() {
                kws[ki].classList.remove('active');
                ki = (ki + 1) % kws.length;
                kws[ki].classList.add('active');
            }, 1800);

            /* Scroll reveal */
            var els = document.querySelectorAll('.fade-up');
            els.forEach(function(el) {
                el.classList.add('will-anim');
            });
            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        setTimeout(function() {
                            e.target.classList.add('in-view');
                        }, 60);
                        obs.unobserve(e.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -40px 0px'
            });
            els.forEach(function(el) {
                obs.observe(el);
            });

            /* Nav scroll */
            window.addEventListener('scroll', function() {
                document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 60);
            }, {
                passive: true
            });

            /* Hero name flicker on load */
            var n1 = document.querySelector('.hero-n1'),
                n2 = document.querySelector('.hero-n2');
            if (n1 && n2) {
                n1.style.opacity = '0';
                n2.style.opacity = '0';
                setTimeout(function() {
                    n1.style.transition = 'opacity .05s';
                    n1.style.opacity = '1';
                    setTimeout(function() {
                        n1.style.opacity = '0';
                        setTimeout(function() {
                            n1.style.transition = 'opacity .5s';
                            n1.style.opacity = '1';
                            setTimeout(function() {
                                n2.style.transition = 'opacity .5s .1s';
                                n2.style.opacity = '1';
                            }, 180);
                        }, 80);
                    }, 60);
                }, 500);
            }
        })();
    </script>
</body>

</html>