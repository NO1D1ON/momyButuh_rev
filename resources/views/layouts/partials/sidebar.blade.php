<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full px-3 py-4 overflow-y-auto bg-gradient-to-b from-[#F564A9] to-[#ebb2cf]">
        
        <a href="{{ route('dashboard') }}" class="flex items-center ps-2.5 mb-5">
            <img src="{{ asset('images/logo.png') }}" class="h-6 me-6 sm:h-9" alt="MomyButuh Logo" />

            <span class="self-center text-xl font-semibold whitespace-nowrap text-white">MomyButuh</span>
        </a>

        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-white/20 group {{ request()->routeIs('dashboard') ? 'bg-[#E9559F]' : '' }}">
                    <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h7.5" />
                    </svg>
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            
            <li class="px-2 pt-4 pb-2 text-xs font-semibold text-white/70 uppercase">Manajemen Data</li>
            
            <li>
                <a href="{{ route('babysitters.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-white/20 group {{ request()->routeIs('babysitters.*') ? 'bg-[#E9559F]' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-white w-6 h-6" viewBox="0 0 256 256">
                    <path fill="currentColor" d="M160 32h-8a16 16 0 0 0-16 16v56H55.2A40.07 40.07 0 0 0 16 72a8 8 0 0 0 0 16a24 24 0 0 1 24 24a80.09 80.09 0 0 0 80 80h40a80 80 0 0 0 0-160m63.48 72h-56.67l41.86-33.49A63.73 63.73 0 0 1 223.48 104M160 48a63.59 63.59 0 0 1 36.69 11.61L152 95.35V48Zm0 128h-40a64.09 64.09 0 0 1-63.5-56h167a64.09 64.09 0 0 1-63.5 56m-56 48a16 16 0 1 1-16-16a16 16 0 0 1 16 16m104 0a16 16 0 1 1-16-16a16 16 0 0 1 16 16"/>
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Data Babysitter</span>
                </a>
            </li>

            <li>
                <a href="{{ route('users.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-white/20 group {{ request()->routeIs('users.*') ? 'bg-[#E9559F]' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-white w-6 h-6" width="200" height="200" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M7 9a2.5 2.5 0 1 0 0-5a2.5 2.5 0 0 0 0 5Zm0 2a4.5 4.5 0 1 1 0-9a4.5 4.5 0 0 1 0 9Zm10.5 2a2 2 0 1 0 0-4a2 2 0 0 0 0 4Zm0 2a4 4 0 1 1 0-8a4 4 0 0 1 0 8Zm2.5 6v-.5a2.5 2.5 0 0 0-5 0v.5h-2v-.5a4.5 4.5 0 1 1 9 0v.5h-2Zm-10 0v-4a3 3 0 1 0-6 0v4H2v-4a5 5 0 0 1 10 0v4h-2Z"/>
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Data Orang Tua</span>
                </a>
            </li>

            <li class="px-2 pt-4 pb-2 text-xs font-semibold text-white/70 uppercase">Keuangan</li>
            
            <li>
                <a href="{{ route('transactions.index') }}" class="flex items-center p-2 text-white transition duration-75 rounded-lg group hover:bg-white/20 {{ request()->routeIs('transactions.*') ? 'bg-[#E9559F]' : '' }}">
                    <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                    <span class="ms-3">Semua Transaksi</span>
                </a>
            </li>

            <li>
                <a href="{{ route('bookings.index') }}" class="flex items-center p-2 text-white transition duration-75 rounded-lg group hover:bg-white/20 {{ request()->routeIs('bookings.*') ? 'bg-[#E9559F]' : '' }}">
                    <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0h18" />
                    </svg>
                    <span class="ms-3">Riwayat Booking</span>
                </a>
            </li>
            
            <li>
                <a href="{{ route('topups.index') }}" class="flex items-center p-2 text-white transition duration-75 rounded-lg group hover:bg-white/20 {{ request()->routeIs('topups.*') ? 'bg-[#E9559F]' : '' }}">
                    <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                    </svg>
                    <span class="ms-3">Manajemen Top Up</span>
                </a>
            </li>
        </ul>
    </div>
</aside>