<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal - PT Mekar Armada Jaya')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            blue: '#3b82f6',
                            purpleBlue: '#6366f1',
                            red: '#e11d48',
                            dark: '#0f172a',
                            lightBg: '#f8fafc'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .bg-gradient-soft {
            background: linear-gradient(135deg, rgba(238,242,255,0.8) 0%, rgba(255,255,255,1) 50%, rgba(255,241,242,0.6) 100%);
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 font-sans antialiased flex flex-col min-h-screen">

@include('admin.partials.topnav')

    <!-- MAIN CONTENT CONTAINERS -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full space-y-8">
        @if (session('sukses'))
            <div class="p-3.5 rounded-xl text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-start space-x-2.5">
                <i class="fa-solid fa-circle-check text-base text-emerald-600 mt-0.5"></i>
                <div class="leading-relaxed">{{ session('sukses') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="p-3.5 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200 flex items-start space-x-2.5">
                <i class="fa-solid fa-circle-xmark text-base text-rose-600 mt-0.5"></i>
                <div class="leading-relaxed">
                    <strong>Mohon periksa kembali:</strong>
                    <ul class="list-disc ml-4 mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

@stack('modals')
@stack('scripts')
</body>
</html>