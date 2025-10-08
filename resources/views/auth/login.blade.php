<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Step Service Part System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;900&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .bg-image {
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('{{ asset('images/bg-4.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 15px;
            position: relative;
        }

        .home-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .home-button:hover {
            transform: translateY(-3px);
            background-color: #ffffff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .home-icon {
            width: 24px;
            height: 24px;
            fill: #00b4d8;
            transition: fill 0.3s ease;
        }

        .home-button:hover .home-icon {
            fill: #0077b6;
        }

        .login-container {
            background: #ffffff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 350px;
            margin: auto;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo img {
            height: 50px;
            width: auto;
            margin-bottom: 0.5rem;
        }

        .logo-text {
            text-align: center;
            position: relative;
        }

        .logo-subtitle {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: 900;
            color: #1a1a1a;
            letter-spacing: 2px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .logo-subtitle span {
            display: block;
        }

        .logo-subtitle .system-text {
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 3px;
            color: #00b4d8;
            margin-top: 0.3rem;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-label {
            display: block;
            margin-bottom: 0.4rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .text-input {
            width: 100%;
            padding: 0.55rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9fafb;
            color: #333;
            font-family: 'Poppins', sans-serif;
            font-size: 0.90rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .text-input:focus {
            outline: none;
            border-color: #00b4d8;
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.1);
        }

        .primary-button {
            background: linear-gradient(90deg, #00b4d8, #0077b6);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            transition: transform 0.2s ease, background 0.3s ease;
        }

        .primary-button:hover {
            transform: translateY(-2px);
            background: linear-gradient(90deg, #0096c7, #005f8c);
        }

        .primary-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .notification {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            display: flex;
            align-items: flex-start;
        }

        .error-notification {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #dc2626;
        }

        .success-notification {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #15803d;
        }

        .notification-icon {
            margin-right: 0.5rem;
            min-width: 16px;
            height: 16px;
            margin-top: 2px;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 0.2rem;
            font-size: 0.85rem;
        }

        .error-title {
            color: #ef4444;
        }

        .success-title {
            color: #22c55e;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .remember-me input {
            margin-right: 0.5rem;
            width: 16px;
            height: 16px;
            accent-color: #00b4d8;
        }

        .remember-me span {
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #555;
        }

        .has-error {
            border-color: #ef4444;
        }

        @media screen and (max-height: 600px) {
            .login-container {
                padding: 1.5rem;
            }
            
            .logo img {
                height: 40px;
            }
            
            .logo-subtitle {
                font-size: 1rem;
            }
            
            .input-group {
                margin-bottom: 0.8rem;
            }

            .notification {
                padding: 0.6rem;
                margin-bottom: 0.8rem;
            }
            
            .home-button {
                width: 40px;
                height: 40px;
                top: 15px;
                left: 15px;
            }
            
            .home-icon {
                width: 20px;
                height: 20px;
            }
        }

        @media screen and (max-height: 480px) {
            .bg-image {
                padding: 8px;
            }

            .login-container {
                padding: 1rem;
                max-width: 300px;
            }
            
            .logo-container {
                margin-bottom: 1rem;
            }
            
            .logo img {
                height: 35px;
            }
            
            .logo-subtitle {
                font-size: 0.9rem;
            }
            
            .logo-subtitle .system-text {
                font-size: 0.75rem;
            }

            .input-label {
                font-size: 0.8rem;
                margin-bottom: 0.3rem;
            }

            .text-input {
                padding: 0.6rem;
                font-size: 0.9rem;
            }

            .primary-button {
                padding: 0.6rem;
                font-size: 0.9rem;
            }

            .notification {
                padding: 0.5rem;
                margin-bottom: 0.6rem;
                font-size: 0.75rem;
            }
            
            .notification-title {
                font-size: 0.8rem;
            }
            
            .home-button {
                width: 36px;
                height: 36px;
                top: 10px;
                left: 10px;
            }
            
            .home-icon {
                width: 18px;
                height: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-image">
        {{-- <a href="{{ url('/') }}" class="home-button" title="Kembali ke Halaman Utama">
            <svg class="home-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 2.1L1 12h3v9h7v-6h2v6h7v-9h3L12 2.1zm0 2.691l6 5.4V19h-3v-6H9v6H6v-8.809l6-5.4z"/>
            </svg>
        </a> --}}
        
        <div class="login-container">
            <div class="logo-container">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo">
                </div>
                <div class="logo-text">
                    <p class="logo-subtitle">
                        <span>Service Part</span>
                        <span class="system-text">SYSTEM</span>
                    </p>
                </div>
            </div>

            @if (session('status'))
                <div class="notification success-notification">
                    <svg class="notification-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="notification-content">
                        <div class="notification-title success-title">Berhasil</div>
                        <div>{{ session('status') }}</div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="notification error-notification">
                    <svg class="notification-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="notification-content">
                        <div class="notification-title error-title">Login Gagal</div>
                        <div>Username atau password salah</div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="input-group">
                    <label for="username" class="input-label">Username</label>
                    <input id="username" class="text-input {{ $errors->has('username') ? 'has-error' : '' }}" type="text" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">Password</label>
                    <input id="password" class="text-input {{ $errors->has('password') ? 'has-error' : '' }}" type="password" name="password" required autocomplete="current-password">
                </div>

                <div class="remember-me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>Remember me</span>
                </div>

                <div>
                    <button type="submit" class="primary-button">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>