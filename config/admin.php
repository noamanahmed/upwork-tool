<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Personal Information
    |--------------------------------------------------------------------------
    */
    'personal' => [
        'first_name' => 'Nauman',
        'last_name' => 'Ahmed',
        'date_of_birth' => '1992-10-31', // optional
        'city' => 'Lahore',
        'state' => 'Punjab',
        'country' => 'Pakistan',
        'title' => 'Senior Full Stack Developer / Architect',
        'experience_years' => 8,
        'timezone' => 'Asia/Karachi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Introduction (AI will pick selectively)
    |--------------------------------------------------------------------------
    */
    'introduction' => [
        'I am a Full Stack Developer with 8+ years of experience building scalable applications.',
        'I specialize in Laravel, Vue.js, and backend system architecture.',
        'I have also worked extensively on performance optimization, infrastructure setup, and complex API integrations.',
        'WordPress development is another area of expertise, with experience managing 2000+ sites and building custom plugins/themes.',
        'I focus on performance optimization, API development, and solving complex technical problems.',
        'I have hands-on experience with AWS, Kubernetes, Docker, and CI/CD pipelines.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Skills
    |--------------------------------------------------------------------------
    */
    'skills' => [

        'backend' => [
            'Laravel',
            'Lumen',
            'CodeIgniter',
            'Node.js',
            'Express.js',
            'REST APIs',
            'Microservices',
        ],

        'frontend' => [
            'Vue.js',
            'Qwik',
            'JavaScript',
            'jQuery',
            'SCSS',
            'SASS',
            'Tailwind CSS',
            'Bootstrap',
            'HTML/CSS',
        ],

        'databases' => [
            'MySQL',
            'PostgreSQL',
            'Sqlite',
            'MongoDB',
            'Redis',
        ],

        'devops' => [
            'AWS',
            'Linux',
            'CI/CD',
            'Docker',
            'Kubernetes',
            'Rancher',
        ],

        'cms' => [
            'WordPress',
            'Custom Plugin Development',
            'Custom Theme Development',
            'Multi Site Management',
            'Advanced Custom Fields (ACF)',
        ],

        'advanced' => [
            'Performance Optimization',
            'System Architecture',
            'Reverse Engineering',
            'Automation Bots',
            'Web Scraping',
        ],
        'page_speed_optimization' => [
            'Image Optimization',
            'Code Minification',
            'Critical CSS',
            'Lazy Loading',
            'Caching Strategies',
            '90+ Lighthouse Performance Scores',
        ],
        'infrastructure' => [
            'server_setup' => [
                'Shared Hosting Setup(WHM)',
                'VPS Setup (KVM, OpenVZ, LXC)',
                'Dedicated Server Setup (Baremetal)',
                'Serverless Deployments (AWS Lambda, Cloudflare Workers)',
            ],
            'web_servers' => [
                'Apache Configuration',
                'Nginx Configuration',
                'Caddy Server',
            ],

            'dns' => [
                'A, AAAA, CNAME, MX, TXT Records configuration',
                'rDNS Setup',
                'Email DNS configuration (SPF, DKIM, DMARC)',
            ],

            'caching' => [
                'Opcode Cache (OPcache)',
                'Memcached',
                'Redis Caching',
            ],

            'control_panels' => [
                'cPanel',
                'Plesk',
                'DirectAdmin',
                'Custom Control Panels',
            ],

            'packages' => [
                'Linux Package Installation',
                'Service Configuration',
            ],

            'remote_access' => [
                'VNC (noVNC, TightVNC)',
                'X2Go',
                'TailScale VPN',
                'RDP Setup',
            ],

            'vpn' => [
                'WireGuard',
                'OpenVPN',
                'SoftEther',
            ],

            'ssl' => [
                'Let’s Encrypt SSL',
                'Wildcard SSL',
                'Paid SSL Installation',
            ],

            'proxy' => [
                'Squid Proxy',
                'Shadowsocks',
                'Dante',
            ],

            'security' => [
                'ClamAV Antivirus Setup',
                'Firewall Configuration (iptables, UFW)',
                'Server Hardening',
            ],

            'email' => [
                'SPF Configuration',
                'DKIM Setup',
                'Reverse DNS (rDNS)',
                'Mail Servers Setup',
                'Roundcube',
                'SquirrelMail',
            ],

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Key Achievements (Used for trust building)
    |--------------------------------------------------------------------------
    */
    'achievements' => [
        'backend' => [
            'Reduced API response time (TTFB) by 90%',
            'Improved database performance by up to 500%',
        ],
        'infrastructure' => [
            'Implemented full monitoring and observability using NewRelic',
            'Reduced production error rates by 70%',
            'Deployed scalable AWS cloud architectures',
        ],
        'cms' => [
            'Managed and maintained 2000+ WordPress websites',
            'Developed custom plugins for complex content management',
        ],
        'frontend' => [
            'Achieved 90+ Lighthouse Performance Scores for complex web apps',
            'Designed modular and reusable Vue.js/React component libraries',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Certifications
    |--------------------------------------------------------------------------
    */
    'certifications' => [
        [
            'name' => 'AWS Certified Developer',
            'issuer' => 'Amazon Web Services',
            'year' => 2023,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Work Experience
    |--------------------------------------------------------------------------
    */
    'experiences' => [

        [
            'role' => 'Full Stack Developer / Architect',
            'company' => 'Nevron d.o.o',
            'start_date' => '2023-09-01',
            'end_date' => null, // null = currently working
            'highlights' => [
                'Designed microservices architecture using Laravel and Lumen',
                'Reduced API TTFB by 90%',
                'Optimized database queries using recursive CTEs',
                'Reduced system errors by 70%',
                'Integrated Flussonic DVR streaming',
                'Handled infrastructure issues using Rancher',
            ],
        ],

        [
            'role' => 'Full Stack Developer / Associate Architect',
            'company' => 'Stealth Startup',
            'start_date' => '2022-09-01',
            'end_date' => '2023-09-01',
            'highlights' => [
                'Implemented NewRelic monitoring for full observability',
                'Integrated shipping APIs (Ajex, Aramex)',
                'Optimized SQL queries using full-text search',
                'Worked on quick commerce fulfillment systems',
            ],
        ],

        [
            'role' => 'Senior Software Engineer',
            'company' => 'AllShore / DatumSquare',
            'start_date' => '2021-01-01',
            'end_date' => '2022-08-01',
            'highlights' => [
                'Converted legacy Zend project to Laravel',
                'Managed 2000+ WordPress sites',
                'Built Node.js microservices with Kafka and Docker',
                'Worked on performance optimization projects',
            ],
        ],

        [
            'role' => 'PHP Developer',
            'company' => 'AirSchool (MetaSchool)',
            'start_date' => '2020-06-01',
            'end_date' => '2020-08-01',
            'highlights' => [
                'Built e-learning platform features in Laravel',
                'Implemented live classroom using Agora SDK',
                'Managed AWS S3 storage and infrastructure',
            ],
        ],

        [
            'role' => 'Freelance Developer',
            'company' => 'Self-employed',
            'start_date' => '2019-01-01',
            'end_date' => null,
            'highlights' => [
                'Developed Laravel APIs and admin dashboards',
                'Built automation bots and scraping tools',
                'Reverse engineered mobile apps for data extraction',
                'Delivered WordPress custom plugins and themes',
            ],
        ],

    ],
    /*
    |--------------------------------------------------------------------------
    | Case Studies (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */
    'case_studies' => [

        [
            'title' => 'HIPAA-Compliant Healthcare Platform (Shuttle Health)',
            'problem' => 'Needed a secure and scalable healthcare platform connecting hospitals, patients, and medical manufacturers with strict compliance requirements.',
            'solution' => 'Developed a HIPAA-compliant system using Laravel and React, integrated third-party services like Brightree and SignalWire, and deployed on AWS with CI/CD pipelines.',
            'result' => 'Delivered a secure, scalable healthcare ecosystem with reliable integrations and production-ready infrastructure.',
            'tech_stack' => [
                'Laravel 10',
                'PHP 8.3',
                'PostgreSQL',
                'React',
                'Chakra UI',
                'AWS',
                'GitHub Actions'
            ],
            'tags' => ['healthcare', 'api', 'aws', 'fullstack', 'security'],
        ],

        [
            'title' => 'Advanced CRM System (VeiligeSportVloer)',
            'problem' => 'Client needed a centralized CRM to manage leads, orders, employees, and business operations.',
            'solution' => 'Built a modular CRM system using Laravel and Vue.js with role-based access, CI/CD pipelines, and optimized database structure.',
            'result' => 'Improved operational efficiency with a scalable and easy-to-manage CRM system.',
            'tech_stack' => [
                'Laravel 10',
                'PHP 8.3',
                'MySQL',
                'Vue.js',
                'Vuetify',
                'Linux VPS',
                'GitLab CI/CD'
            ],
            'tags' => ['crm', 'fullstack', 'backend'],
        ],

        [
            'title' => 'Citizen Application Portal with DocuSign Integration',
            'problem' => 'Complex application workflows with multi-step forms and document signing requirements.',
            'solution' => 'Built a Laravel-based portal integrated with SuiteCRM and DocuSign, including OAuth2 authentication and dynamic application flows.',
            'result' => 'Streamlined application processing and reduced manual work through automation.',
            'tech_stack' => [
                'Laravel 10',
                'PHP 8.2',
                'Blade',
                'jQuery',
                'REST API',
                'OAuth2',
                'SuiteCRM'
            ],
            'tags' => ['api', 'integration', 'crm', 'backend'],
        ],

        [
            'title' => 'E-Commerce Fulfillment & Warehouse Management Platform',
            'problem' => 'Needed a scalable system to manage orders, inventory, returns, and warehouse operations.',
            'solution' => 'Designed a microservices-based system using Laravel/Lumen deployed on AWS ECS with full order lifecycle management.',
            'result' => 'Enabled seamless fulfillment operations with improved scalability and system reliability.',
            'tech_stack' => [
                'Laravel 10',
                'Lumen',
                'MySQL',
                'React',
                'AWS ECS',
                'ECR',
                'Microservices'
            ],
            'tags' => ['microservices', 'ecommerce', 'aws', 'backend'],
        ],

        [
            'title' => 'SaaS Backlink Tracking Tool',
            'problem' => 'Needed a scalable system to track backlinks efficiently with real-time data handling.',
            'solution' => 'Developed a SaaS tool using Laravel with Redis caching for performance optimization.',
            'result' => 'Delivered a fast and scalable backlink tracking system.',
            'tech_stack' => [
                'Laravel',
                'MySQL',
                'Redis'
            ],
            'tags' => ['saas', 'backend', 'performance'],
        ],

        [
            'title' => 'Multilingual Leads Management Dashboard',
            'problem' => 'Client required a multilingual system for managing leads across different regions.',
            'solution' => 'Built a Laravel-based dashboard with multilingual support and optimized UI using Metronic.',
            'result' => 'Enabled efficient lead tracking with support for multiple languages.',
            'tech_stack' => [
                'Laravel 8',
                'MySQL',
                'Blade',
                'jQuery',
                'Metronic'
            ],
            'tags' => ['crm', 'multilingual', 'backend'],
        ],

        [
            'title' => 'Full ERP System (UFOLEP ERP)',
            'problem' => 'Needed an all-in-one ERP solution covering HR, finance, project management, and communication.',
            'solution' => 'Developed a modular ERP system with multiple integrated modules and real-time communication features.',
            'result' => 'Centralized business operations into a single scalable platform.',
            'tech_stack' => [
                'Laravel 8',
                'MySQL',
                'Blade',
                'jQuery'
            ],
            'tags' => ['erp', 'backend', 'fullstack'],
        ],

        [
            'title' => 'Automated Raffle Entry System (800+ Accounts)',
            'problem' => 'Manual raffle entry process was inefficient and not scalable.',
            'solution' => 'Built an automated Laravel dashboard with proxy rotation and multi-account handling.',
            'result' => 'Enabled fully automated high-volume raffle entries with improved success rate.',
            'tech_stack' => [
                'Laravel 8',
                'MySQL',
                'jQuery'
            ],
            'tags' => ['automation', 'bots', 'backend'],
        ],

        [
            'title' => 'WireGuard VPN Server Deployment',
            'problem' => 'Client needed a secure and easy-to-use VPN setup for remote access.',
            'solution' => 'Deployed and configured WireGuard VPN with QR-based client configuration.',
            'result' => 'Delivered secure and user-friendly VPN access for distributed teams.',
            'tech_stack' => [
                'WireGuard',
                'Linux'
            ],
            'tags' => ['vpn', 'devops', 'infrastructure'],
        ],

        [
            'title' => 'LAMP/LEMP Server Deployment',
            'problem' => 'Needed production-ready server setup for web applications.',
            'solution' => 'Configured LAMP/LEMP stack with optimized performance and security settings.',
            'result' => 'Delivered stable and high-performance hosting environments.',
            'tech_stack' => [
                'Linux',
                'Apache',
                'Nginx',
                'MySQL',
                'PHP'
            ],
            'tags' => ['devops', 'server', 'infrastructure'],
        ],

        [
            'title' => 'Proxy Server Infrastructure Setup',
            'problem' => 'Client required scalable proxy solutions for traffic routing.',
            'solution' => 'Deployed Squid HTTP and Dante SOCKS5 proxy servers with optimized configurations.',
            'result' => 'Enabled reliable and scalable proxy infrastructure.',
            'tech_stack' => [
                'Squid',
                'Dante',
                'Linux'
            ],
            'tags' => ['proxy', 'networking', 'devops'],
        ],

        [
            'title' => 'Secure REST API Development',
            'problem' => 'Needed a secure API with strong authentication for sensitive data.',
            'solution' => 'Built REST APIs using Laravel/Lumen with SHA-256 based authentication.',
            'result' => 'Delivered secure and reliable API endpoints for production use.',
            'tech_stack' => [
                'Laravel',
                'Lumen',
                'REST API'
            ],
            'tags' => ['api', 'security', 'backend'],
        ],

        [
            'title' => 'AIMS ERP Voucher Module',
            'problem' => 'ERP system required a structured voucher and accounting module.',
            'solution' => 'Developed a voucher management module integrated into existing ERP workflows.',
            'result' => 'Improved financial tracking and reporting accuracy.',
            'tech_stack' => [
                'Laravel 5.6',
                'AdminLTE'
            ],
            'tags' => ['erp', 'backend'],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Languages
    |--------------------------------------------------------------------------
    */
    'languages' => [
        'English' => 'Fluent',
        'Urdu' => 'Fluent',
        'Punjabi' => 'Intermediate',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hobbies (Human Touch - Optional Use)
    |--------------------------------------------------------------------------
    */
    'hobbies' => [
        'Football',
        'Chess',
        'Traveling',
        'Food',
    ],

    /*
    ||--------------------------------------------------------------------------
    | Job Type to Skill Tag Mapping (Used for AI to select relevant skills)
    ||--------------------------------------------------------------------------
    */
    'job_type_mapping' => [
        'api' => ['backend', 'databases'],
        'wordpress' => ['cms'],
        'performance' => ['advanced', 'databases'],
        'devops' => ['devops', 'infrastructure'],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Behavior Controls (VERY IMPORTANT)
    |--------------------------------------------------------------------------
    */
    'ai_rules' => [
        'max_case_studies' => 3,
        'max_achievements' => 3,
        'use_introduction_lines' => 1,
        'avoid_full_dump' => true,
    ],

];
