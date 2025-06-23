<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full px-3 py-4 overflow-y-auto bg-white shadow-md">
        <a href="{{ route('dashboard') }}" class="flex items-center ps-2.5 mb-5">
            <img src="https://raw.githubusercontent.com/user-attachments/assets/95a79407-1698-4b71-9257-797960339d1b" class="h-6 me-3 sm:h-7" alt="MomyButuh Logo" />
            <span class="self-center text-xl font-semibold whitespace-nowrap text-gray-900">MomyButuh</span>
        </a>
        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('dashboard') ? 'bg-gray-200' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                        <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/><path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.026V10h8.975a1 1 0 0 0 1-.934A8.5 8.5 0 0 0 12.5 0Z"/>
                    </svg>
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            
            <li class="px-2 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase">Manajemen Data</li>
            <li>
                <a href="{{ route('babysitters.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('babysitters.*') ? 'bg-gray-200' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z" clip-rule="evenodd"/></svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Data Babysitter</span>
                </a>
            </li>
            <li>
                <a href="{{ route('users.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('users.*') ? 'bg-gray-200' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18"><path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-2a6.957 6.957 0 0 1 1.264-3H9a7 7 0 0 0-7 7v1a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1a7 7 0 0 0-7-7Z"/></svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Data Orang Tua</span>
                </a>
            </li>

            {{-- MENU DROPDOWN BARU --}}
            <li class="px-2 pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase">Keuangan</li>
            {{-- LINK BARU DI SINI --}}
            <li>
                <a href="{{ route('transactions.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 {{ request()->routeIs('transactions.*') ? 'bg-gray-200' : '' }}">Semua Transaksi</a>
            </li>
            <li>
                <a href="{{ route('bookings.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 {{ request()->routeIs('bookings.*') ? 'bg-gray-200' : '' }}">Riwayat Booking</a>
            </li>
            <li>
                <a href="{{ route('topups.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 {{ request()->routeIs('topups.*') ? 'bg-gray-200' : '' }}">Manajemen Top Up</a>
            </li>
        </ul>
    </div>
</aside>