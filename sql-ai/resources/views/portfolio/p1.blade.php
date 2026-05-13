<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kapil Agarwal — Senior Software & AI Engineer. Building scalable microservices and LLM-powered developer tools for 1M+ users. Bengaluru, India.">
    <title>Kapil Agarwal — Senior Software & AI Engineer | KapilLabs</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700;1,900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/p1.css') }}">

    <!-- GSAP via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
</head>

<body>

    <!-- Cursor glow -->
    <div class="cursor-glow" id="cursorGlow"></div>

    <!-- ===================== NAV ===================== -->
    <nav class="nav" id="mainNav">
        <a href="/" class="nav-logo">Kapil<span>Labs</span></a>

        <ul class="nav-links">
            <li><a href="#about">About</a></li>
            <li><a href="#experience">Experience</a></li>
            <li><a href="#projects">Projects</a></li>
            <li><a href="/ai/">AI Tools</a></li>
            <li><a href="#skills">Skills</a></li>
            <li><a href="#contact" class="nav-cta">Let's talk</a></li>
        </ul>

        <div class="nav-hamburger" id="hamburger" aria-label="Toggle menu" role="button" tabindex="0">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Mobile nav drawer -->
    <div class="nav-drawer" id="navDrawer">
        <a href="#about" class="drawer-link">About</a>
        <a href="#experience" class="drawer-link">Experience</a>
        <a href="#projects" class="drawer-link">Projects</a>
        <a href="/ai/" class="drawer-link">AI Tools</a>
        <a href="#skills" class="drawer-link">Skills</a>
        <a href="#contact" class="drawer-link" style="color: var(--indigo); font-weight: 600;">Let's talk →</a>
    </div>

    <!-- ===================== FLOATING SOCIALS ===================== -->
    <div class="socials-float" id="socialsFloat">
        <a href="https://github.com/while1-KAPIL-do" target="_blank" rel="noopener" aria-label="GitHub">GH</a>
        <a href="https://www.linkedin.com/in/kapil-a-17295a147" target="_blank" rel="noopener" aria-label="LinkedIn">LI</a>
        <div class="bar"></div>
        <a href="mailto:kapillabs.work@gmail.com" aria-label="Email">✉</a>
    </div>

    <!-- ===================== HERO ===================== -->
    <section id="hero">
        <div class="hero-glow-1"></div>
        <div class="hero-glow-2"></div>
        <div class="hero-glow-3"></div>

        <div class="hero-inner">
            <div class="hero-top">
                <div class="hero-badge">
                    <div class="hero-badge-dot"></div>
                    <span>Senior Software &amp; AI Engineer · Bengaluru</span>
                </div>
                <span class="hero-tag-right">Open to collaborate</span>
            </div>

            <div class="hero-name">
                <span class="hero-name-line">Kapil</span>
                <span class="hero-name-line italic">Agarwal</span>
            </div>

            <div class="hero-bottom">
                <div class="hero-desc">
                    <h2>Building systems<br>that scale &amp; think</h2>
                    <p>Microservices, Kafka pipelines &amp; LLM-powered tools — serving 1M+ users at AltInvest and beyond.</p>
                </div>

                <div class="hero-btns">
                    <a href="#projects" class="btn-outline">View Projects</a>
                    <a href="/ai/" class="btn-fill">AI Tools &nbsp;→</a>
                </div>

                <div class="hero-scroll">
                    <div class="scroll-line"></div>
                    <div class="scroll-txt">scroll</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== MARQUEE ===================== -->
    <div class="marquee-wrap" aria-hidden="true">
        <div class="marquee-inner">
            <span>FastAPI</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>LangChain</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Apache Kafka</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>AWS ECS</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>ChromaDB</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Laravel</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Docker</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>RAG &amp; Embeddings</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>MySQL</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Redis</span><span class="dot">&nbsp;✦&nbsp;</span>
            <!-- Duplicate for seamless loop -->
            <span>FastAPI</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>LangChain</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Apache Kafka</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>AWS ECS</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>ChromaDB</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Laravel</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Docker</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>RAG &amp; Embeddings</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>MySQL</span><span class="dot">&nbsp;✦&nbsp;</span>
            <span>Redis</span><span class="dot">&nbsp;✦&nbsp;</span>
        </div>
    </div>

    <!-- ===================== STATEMENT ===================== -->
    <section id="statement">
        <div class="sec-num reveal">01 — What I do</div>
        <h2 class="stmt-h reveal" style="transition-delay: 0.1s">
            5+ years making<br>
            systems that <em>scale,</em><br>
            think &amp; ship.
        </h2>
        <div class="stmt-aside reveal" style="transition-delay: 0.2s">
            <p>From event-driven microservices on Kafka to LLM-powered developer tools — I build things that hold up at 1M+ users.</p>
        </div>
    </section>

    <!-- ===================== EXPERIENCE ===================== -->
    <section id="experience">
        <div class="sec-num reveal">02 — Experience</div>

        <div class="exp-list">

            <!-- AltInvest -->
            <div class="exp-item reveal">
                <div class="exp-left">
                    <div class="exp-dates">Sep 2022 – Present</div>
                    <div class="exp-company">AltInvest</div>
                    <div class="exp-location">formerly PropertyShare · Bengaluru</div>
                </div>
                <div class="exp-right">
                    <div class="exp-role">Software Engineer</div>
                    <div class="exp-bullets">
                        <div class="exp-bullet">Designed event-driven microservices on <strong>Kafka</strong> — async communication across services handling <span class="metric">1M+ users</span>, eliminating tight coupling.</div>
                        <div class="exp-bullet">Refactored legacy systems using Strategy &amp; Factory patterns — reduced production bugs by <span class="metric">50%</span>.</div>
                        <div class="exp-bullet">Built Abstract Factory integration framework — cut third-party onboarding time by <span class="metric">40%</span>.</div>
                        <div class="exp-bullet">Containerized microservices with Docker; designed CI/CD pipelines on AWS — <span class="metric">25%</span> reduction in average API response time.</div>
                    </div>
                </div>
            </div>

            <!-- Mantra Labs -->
            <div class="exp-item reveal" style="transition-delay: 0.08s">
                <div class="exp-left">
                    <div class="exp-dates">Oct 2020 – Aug 2022</div>
                    <div class="exp-company">Mantra Labs</div>
                    <div class="exp-location">Care Health Insurance · Bengaluru</div>
                </div>
                <div class="exp-right">
                    <div class="exp-role">Software Engineer</div>
                    <div class="exp-bullets">
                        <div class="exp-bullet">Integrated WhatsApp, payment gateways &amp; dialer systems — reduced manual operational effort by <span class="metric">35%</span>.</div>
                        <div class="exp-bullet">Optimised large-scale DB queries and indexing — cut data processing time by <span class="metric">40%</span>.</div>
                        <div class="exp-bullet">Designed and implemented <strong>RBAC</strong> and dynamic dashboards with fine-grained access control.</div>
                        <div class="exp-bullet">Established code review culture &amp; testing standards — reduced production defects by <span class="metric">20%</span>.</div>
                    </div>
                </div>
            </div>

            <!-- AppTech -->
            <div class="exp-item reveal" style="transition-delay: 0.16s">
                <div class="exp-left">
                    <div class="exp-dates">Dec 2019 – Jan 2020</div>
                    <div class="exp-company">AppTech Interactive</div>
                    <div class="exp-location">Gwalior</div>
                </div>
                <div class="exp-right">
                    <div class="exp-role">Android Developer Intern</div>
                    <div class="exp-bullets">
                        <div class="exp-bullet">Built and deployed client-facing Android apps — streaming, blogging, EdTech — using Firebase with Meta &amp; Google Ads integration.</div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ===================== PROJECTS ===================== -->
    <section id="projects">
        <div class="projects-hd">
            <div class="sec-num reveal" style="margin-bottom:0">03 — Projects</div>
            <a href="https://github.com/while1-KAPIL-do" target="_blank" rel="noopener" class="reveal">View GitHub &nbsp;↗</a>
        </div>

        <div class="proj-list">

            <div class="proj-item reveal" style="transition-delay:0.06s">
                <span class="proj-num">02</span>
                <div>
                    <div class="proj-name">SQL Voice Assistant</div>
                    <div class="proj-sub">Voice-enabled natural language → SQL for conversational data retrieval without writing queries</div>
                </div>
                <div class="proj-tags">
                    <span class="proj-tag live">Live ✦</span>
                    <span class="proj-tag">NLP</span>
                </div>
                <span class="proj-arrow"><a href="/ai/sql-assistant" target="_blank" rel="noopener" class="reveal">&nbsp;↗</a></span>
            </div>

            <div class="proj-item reveal" style="transition-delay:0.12s">
                <span class="proj-num">03</span>
                <div>
                    <div class="proj-name">LLM Token Dashboard</div>
                    <div class="proj-sub">Centralised real-time monitoring for multi-LLM token usage and cost tracking across providers</div>
                </div>
                <div class="proj-tags">
                    <span class="proj-tag live">Live ✦</span>
                    <span class="proj-tag">Dashboard</span>
                </div>
                <span class="proj-arrow"><a href="/ai/token-dashboard" target="_blank" rel="noopener" class="reveal">&nbsp;↗</a></span>
            </div>

            <div class="proj-item reveal">
                <span class="proj-num">01</span>
                <div>
                    <div class="proj-name">LLDForge</div>
                    <div class="proj-sub">AI-powered PRD-to-LLD generator · FastAPI, LangChain, ChromaDB, OpenAI/Anthropic · AWS ECS + Docker</div>
                </div>
                <div class="proj-tags">
                    <span class="proj-tag">AI / LLM</span>
                    <span class="proj-tag wip">In Progress</span>
                </div>
                <!-- <span class="proj-arrow">↗</span> -->
            </div>

        </div>
    </section>

    <!-- ===================== VIDEO — WORKSPACE ===================== -->
    <div id="workspace">
        <video autoplay muted loop playsinline preload="none">
            {{-- Replace with your own workspace video in public/videos/workspace.mp4 --}}
            <source src="{{ asset('videos/workspace.mp4') }}" type="video/mp4">
            {{-- Fallback: juanmora's desk video while you set up your own --}}
            <source src="https://juanmora.co/videos-work/desk_jm3.mp4" type="video/mp4">
        </video>
        <div class="vid-overlay">
            <div class="vid-label">04 — The workspace</div>
            <div class="vid-headline">Where the systems<br><span>get built.</span></div>
        </div>
    </div>

    <!-- ===================== AI TOOLS ===================== -->
    <section id="ai-tools">
        <div class="ai-left reveal">
            <div class="ai-label">05 — AI Tools</div>
            <h2 class="ai-h">Tools I built<br>that <em>actually work</em></h2>
            <p class="ai-sub">Live AI-powered tools at <strong style="color:#f5f0e8">kapillabs.com/ai/</strong> — built with LangChain, FastAPI, OpenAI &amp; Anthropic APIs.</p>
            <a href="/ai/" class="ai-cta">Explore all AI tools &nbsp;→</a>
        </div>

        <div class="ai-right reveal" style="transition-delay: 0.15s">
            <a href="/ai/sql-assistant" target="_blank" class="ai-tool-card">
                <div class="ai-tool-info">
                    <div class="ai-tool-name">SQL Voice Assistant</div>
                    <div class="ai-tool-desc">Voice → natural language → SQL queries</div>
                </div>
                <span class="ai-tool-badge badge-live">Live ✦</span>
            </a>

            <a href="/ai/token-dashboard" target="_blank" class="ai-tool-card">
                <div class="ai-tool-info">
                    <div class="ai-tool-name">LLM Token Dashboard</div>
                    <div class="ai-tool-desc">Real-time multi-provider cost tracking</div>
                </div>
                <span class="ai-tool-badge badge-live">Live ✦</span>
            </a>

            <div class="ai-tool-card">
                <div class="ai-tool-info">
                    <div class="ai-tool-name">LLDForge</div>
                    <div class="ai-tool-desc">PRD document → Low-Level Design artifacts</div>
                </div>
                <span class="ai-tool-badge badge-wip">Building</span>
                <!-- KEEPING this AS i may need in future : <span class="ai-tool-badge badge-soon">Soon</span> -->
            </div>
        </div>
    </section>

    <!-- ===================== ABOUT ===================== -->
    <section id="about">
        <div class="reveal">
            <div class="sec-num" style="margin-bottom:20px">06 — About</div>
            <h2 class="about-h">
                Good engineering<br>
                <em>takes taste.</em><br>
                I have both.
            </h2>
            <p class="about-p">Backend engineer with 5+ years shipping scalable microservices, event-driven systems, and AI tooling serving 1M+ users. Currently deepening expertise in LLMs, RAG pipelines, and developer workflow automation.</p>
            <p class="about-p">B.E. Computer Science — SRCM Gwalior, RGPV University, 2020. Outside of code: Piano · Strength Training · Running · Pickleball · Audiobooks.</p>
            <a href="mailto:kapillabs.work@gmail.com" class="about-link">Get in touch &nbsp;→</a>
        </div>

        <div class="about-stats reveal" style="transition-delay: 0.15s">
            <div class="stat-card">
                <div class="stat-num">5<span>+</span></div>
                <div class="stat-label">Years building production systems</div>
            </div>
            <div class="stat-card">
                <div class="stat-num">1M<span>+</span></div>
                <div class="stat-label">Users served across platforms</div>
            </div>
            <div class="stat-card">
                <div class="stat-num">50<span>%</span></div>
                <div class="stat-label">Bug reduction at AltInvest</div>
            </div>
            <div class="stat-card">
                <div class="stat-num">40<span>%</span></div>
                <div class="stat-label">Faster third-party onboarding</div>
            </div>
        </div>
    </section>

    <!-- ===================== SKILLS ===================== -->
    <section id="skills">
        <div class="sec-num reveal">07 — Skills &amp; Stack</div>
        <div class="skills-grid">

            <div class="skill-group reveal">
                <div class="skill-group-title">Backend</div>
                <div class="skill-list">
                    <div class="skill-item">
                        <div class="skill-dot"></div>FastAPI
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>Laravel
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>REST APIs
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>Microservices
                    </div>
                </div>
            </div>

            <div class="skill-group reveal" style="transition-delay:0.07s">
                <div class="skill-group-title">AI / LLM</div>
                <div class="skill-list">
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>LangChain
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>RAG
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>Vector Search
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>ChromaDB
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>Embeddings
                    </div>
                </div>
            </div>

            <div class="skill-group reveal" style="transition-delay:0.14s">
                <div class="skill-group-title">Streaming</div>
                <div class="skill-list">
                    <div class="skill-item">
                        <div class="skill-dot"></div>Apache Kafka
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>Event-Driven Arch
                    </div>
                </div>
            </div>

            <div class="skill-group reveal" style="transition-delay:0.21s">
                <div class="skill-group-title">Cloud &amp; DevOps</div>
                <div class="skill-list">
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>AWS ECS
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>AWS S3
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>Docker
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot teal"></div>CI/CD Pipelines
                    </div>
                </div>
            </div>

            <div class="skill-group reveal" style="transition-delay:0.28s">
                <div class="skill-group-title">Data &amp; Tools</div>
                <div class="skill-list">
                    <div class="skill-item">
                        <div class="skill-dot"></div>MySQL
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>Redis
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>Git
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>Postman
                    </div>
                    <div class="skill-item">
                        <div class="skill-dot"></div>JIRA
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ===================== FOOTER / CONTACT ===================== -->
    <footer id="contact">
        <div class="footer-name-row">
            <div class="footer-big-name">Kapil</div>
            <div class="footer-center">
                <div class="footer-cta-label">Want to build something together?</div>
                <a href="mailto:kapillabs.work@gmail.com" class="footer-cta-email">kapillabs.work@gmail.com</a>
            </div>
            <div class="footer-big-name it">Agarwal</div>
        </div>

        <div class="footer-bottom">
            <span class="footer-copy">© KapilLabs 2026 · Bengaluru, India</span>

            <div class="footer-stack">
                <span>Laravel</span>
                <span>Blade</span>
                <span>GSAP</span>
            </div>

            <div class="footer-links">
                <a href="https://github.com/while1-KAPIL-do" target="_blank" rel="noopener">GitHub</a>
                <a href="https://www.linkedin.com/in/kapil-a-17295a147" target="_blank" rel="noopener">LinkedIn</a>
                <a href="mailto:kapillabs.work@gmail.com">Email</a>
            </div>
        </div>
    </footer>

    <!-- ===================== SCRIPTS ===================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ---- Hamburger / mobile drawer ---- */
            const hamburger = document.getElementById('hamburger');
            const drawer = document.getElementById('navDrawer');

            if (hamburger && drawer) {
                hamburger.addEventListener('click', function() {
                    drawer.classList.toggle('open');
                });
                document.querySelectorAll('.drawer-link').forEach(function(link) {
                    link.addEventListener('click', function() {
                        drawer.classList.remove('open');
                    });
                });
            }

            /* ---- Cursor glow ---- */
            const glow = document.getElementById('cursorGlow');
            if (glow) {
                document.addEventListener('mousemove', function(e) {
                    glow.style.left = e.clientX + 'px';
                    glow.style.top = e.clientY + 'px';
                });
            }

            /* ---- Scroll reveal — SAFE version ----
            1. Add .will-animate to all .reveal elements (hides them)
            2. IntersectionObserver adds .visible when in viewport
            This order ensures elements are NEVER stuck invisible.
            ---- */
            const reveals = document.querySelectorAll('.reveal');

            // Step 1: mark them for animation (CSS hides only after this class exists)
            reveals.forEach(function(el) {
                el.classList.add('will-animate');
            });

            // Step 2: observe and reveal
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        // Small delay so the hide state has painted first
                        setTimeout(function() {
                            entry.target.classList.add('visible');
                        }, 40);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -32px 0px'
            });

            reveals.forEach(function(el) {
                observer.observe(el);
            });

            /* ---- Nav background on scroll ---- */
            var nav = document.getElementById('mainNav');
            if (nav) {
                window.addEventListener('scroll', function() {
                    nav.style.background = window.scrollY > 40 ?
                        'rgba(245,240,232,0.97)' :
                        'rgba(245,240,232,0.88)';
                }, {
                    passive: true
                });
            }

            /* ---- GSAP animations (only if GSAP loaded) ---- */
            window.addEventListener('load', function() {
                if (typeof gsap === 'undefined') return;
                if (typeof ScrollTrigger !== 'undefined') {
                    gsap.registerPlugin(ScrollTrigger);
                }

                /* Hero entrance — staggered */
                var tl = gsap.timeline({
                    defaults: {
                        ease: 'power3.out'
                    }
                });
                tl.from('.hero-badge', {
                        opacity: 0,
                        y: 18,
                        duration: 0.65,
                        delay: 0.15
                    })
                    .from('.hero-name-line', {
                        opacity: 0,
                        y: 55,
                        duration: 0.85,
                        stagger: 0.14
                    }, '-=0.3')
                    .from('.hero-desc', {
                        opacity: 0,
                        y: 20,
                        duration: 0.6
                    }, '-=0.4')
                    .from('.hero-btns', {
                        opacity: 0,
                        y: 16,
                        duration: 0.55
                    }, '-=0.35')
                    .from('.hero-scroll', {
                        opacity: 0,
                        duration: 0.5
                    }, '-=0.2')
                    .from('.socials-float', {
                        opacity: 0,
                        x: -14,
                        duration: 0.6
                    }, '-=0.5');

                /* Parallax glow orbs */
                if (typeof ScrollTrigger !== 'undefined') {
                    gsap.to('.hero-glow-1', {
                        yPercent: -25,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '#hero',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: 1.5
                        }
                    });
                    gsap.to('.hero-glow-2', {
                        yPercent: -18,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '#hero',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: 2
                        }
                    });

                    /* Statement scrub */
                    gsap.from('.stmt-h', {
                        xPercent: -3,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '#statement',
                            start: 'top bottom',
                            end: 'bottom top',
                            scrub: 1
                        }
                    });

                    /* Stat cards */
                    gsap.from('.stat-card', {
                        opacity: 0,
                        scale: 0.96,
                        y: 18,
                        duration: 0.55,
                        stagger: 0.09,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: '#about',
                            start: 'top 78%'
                        }
                    });
                }
            });

        });
    </script>

</body>

</html>