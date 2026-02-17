<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FleetBill</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap');

        :root {
            --bg-1: #061119;
            --bg-2: #152a33;
            --bg-3: #0d1b2a;
            --text: #f7fafc;
            --muted: #a8c3cc;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            display: grid;
            place-items: center;
            color: var(--text);
            background:
                radial-gradient(circle at 18% 15%, #2e7d8f55 0%, transparent 38%),
                radial-gradient(circle at 85% 80%, #ff8a3d33 0%, transparent 42%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2) 45%, var(--bg-3));
            font-family: "Bebas Neue", sans-serif;
            overflow: hidden;
        }

        .glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: conic-gradient(from 100deg at 50% 50%, #42d9ff22, #ff7f5022, #ffe06622, #42d9ff22);
            filter: blur(80px);
            animation: drift 14s linear infinite;
        }

        .wrap {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
        }

        .title {
            margin: 0;
            font-size: clamp(4rem, 20vw, 15rem);
            letter-spacing: 0.08em;
            line-height: 0.95;
            color: var(--text);
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            transition: transform 180ms ease;
            cursor: default;
            user-select: none;
        }

        .title:hover {
            animation: fleetbill-rainbow 1.8s linear infinite;
            transform: translateY(-3px);
        }

        .subtitle {
            margin-top: 0.75rem;
            font-size: clamp(0.9rem, 2.2vw, 1.4rem);
            letter-spacing: 0.2em;
            color: var(--muted);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            text-transform: uppercase;
        }

        @keyframes fleetbill-rainbow {
            0% { color: #5be7ff; }
            16% { color: #57ffb0; }
            32% { color: #fff36a; }
            48% { color: #ff9d57; }
            64% { color: #ff6f91; }
            80% { color: #bc7bff; }
            100% { color: #5be7ff; }
        }

        @keyframes drift {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.08); }
            100% { transform: rotate(360deg) scale(1); }
        }
    </style>
</head>
<body>
    <div class="glow" aria-hidden="true"></div>
    <main class="wrap">
        <h1 class="title">FleetBill</h1>
        <p class="subtitle">SaaS Invoicing for Fleet Operations</p>
    </main>
</body>
</html>
