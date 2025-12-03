<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portfolio - K22 Store')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    @yield('extra-css')
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('portfolio.index') }}" class="text-2xl font-bold text-blue-600">K22 Store</a>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ route('portfolio.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">
                        Portfolio
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">K22 Store</h3>
                    <p class="text-gray-400">Premium product catalog with quality and variety.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('portfolio.index') }}" class="hover:text-white">Portfolio</a></li>
                        <li><a href="{{ route('portfolio.index') }}" class="hover:text-white">Products</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Telegram Channel: https://t.me/K22_store</li>
                        <li>Phone: 089/098 443 498</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Follow Us</h4>
                    <div class="flex gap-4 text-gray-400">
                        <a href="https://web.facebook.com/K22Store" target="_blank" rel="noopener noreferrer" class="hover:text-white">Facebook</a>
                        <a href="#" class="hover:text-white">Tik Tok</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 K22 Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @yield('extra-js')
</body>
</html>
