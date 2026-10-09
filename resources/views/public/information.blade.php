<x-public-layout title="Pusat Informasi & Regulasi" active="information">
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6" x-data="{ activeTab: 'kebijakan' }">
        <div class="flex flex-col md:flex-row gap-12">
            
            <!-- Sidebar Navigasi Tab -->
            <div class="w-full md:w-64 shrink-0">
                <div class="sticky top-24">
                    <h3 class="text-xs font-semibold text-[#86868B] tracking-wider uppercase mb-4">Pusat Informasi</h3>
                    <ul class="space-y-3 text-sm font-medium">
                        <li>
                            <a href="#" @click.prevent="activeTab = 'kebijakan'" 
                               class="transition block" 
                               :class="activeTab === 'kebijakan' ? 'text-[#1D1D1F]' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                               Kebijakan Privasi
                            </a>
                        </li>
                        <li>
                            <a href="#" @click.prevent="activeTab = 'syarat'" 
                               class="transition block"
                               :class="activeTab === 'syarat' ? 'text-[#1D1D1F]' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                               Syarat & Ketentuan
                            </a>
                        </li>
                        <li>
                            <a href="#" @click.prevent="activeTab = 'panduan'" 
                               class="transition block"
                               :class="activeTab === 'panduan' ? 'text-[#1D1D1F]' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                               Panduan Peminjaman
                            </a>
                        </li>
                        <li>
                            <a href="#" @click.prevent="activeTab = 'lapor'" 
                               class="transition block"
                               :class="activeTab === 'lapor' ? 'text-[#1D1D1F]' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                               Lapor Kerusakan (Helpdesk)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Area Konten -->
            <div class="flex-1 max-w-3xl relative">
                
                <!-- Konten: Kebijakan Privasi -->
                <div x-show="activeTab === 'kebijakan'" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4" 
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="display: none;">
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Kebijakan Privasi</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">1. Pengumpulan Data</h2>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Curabitur pretium tincidunt lacus. Nulla gravida orci a odio.</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">2. Penggunaan Informasi</h2>
                        <p>Nullam varius, turpis et commodo pharetra, est eros bibendum elit, nec luctus magna felis sollicitudin mauris. Integer in mauris eu nibh euismod gravida. Duis ac tellus et risus vulputate vehicula. Donec lobortis risus a elit. Etiam tempor. Ut ullamcorper, ligula eu tempor congue, eros est euismod turpis, id tincidunt sapien risus a quam.</p>
                        
                        <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 mt-8">
                            <p class="text-sm m-0">Maecenas fermentum consequat mi. Donec fermentum. Pellentesque malesuada nulla a mi. Duis sapien sem, aliquet nec, commodo eget, consequat quis, neque.</p>
                        </div>
                    </div>
                </div>

                <!-- Konten: Syarat & Ketentuan -->
                <div x-show="activeTab === 'syarat'" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4" 
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="display: none;" x-cloak>
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Syarat & Ketentuan</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>Aliquam erat volutpat. Suspendisse pulvinar, augue ac venenatis condimentum, sem libero volutpat nibh, nec pellentesque velit pede quis nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce id purus.</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Kewajiban Pengguna</h2>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Donec interdum, metus et hendrerit aliquet, dolor diam sagittis ligula.</li>
                            <li>Nam non magna at sem dignissim tempor.</li>
                            <li>Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue.</li>
                            <li>Phasellus ullamcorper ipsum rutrum nunc.</li>
                        </ul>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Sanksi Pelanggaran</h2>
                        <p>Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum. Nullam nulla eros, ultricies sit amet, nonummy id, imperdiet feugiat, pede.</p>
                    </div>
                </div>

                <!-- Konten: Panduan Peminjaman -->
                <div x-show="activeTab === 'panduan'" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4" 
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="display: none;" x-cloak>
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Panduan Peminjaman</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>Sed egestas, ante et vulputate volutpat, eros pede semper est, vitae luctus metus libero eu augue. Morbi purus libero, faucibus adipiscing, commodo quis, gravida id, est. Sed lectus.</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Langkah-Langkah Reservasi</h2>
                        <ol class="list-decimal pl-5 space-y-4">
                            <li><strong>Tahap Pertama:</strong> Aenean posuere, tortor sed cursus feugiat, nunc augue blandit nunc, eu sollicitudin urna dolor sagittis lacus.</li>
                            <li><strong>Tahap Kedua:</strong> Donec elit libero, sodales nec, volutpat a, suscipit non, turpis. Nullam sagittis.</li>
                            <li><strong>Tahap Ketiga:</strong> Suspendisse enim turpis, dictum sed, iaculis a, condimentum nec, nisi. Praesent nec nisl a purus blandit viverra.</li>
                        </ol>
                        
                        <p>Praesent nonummy mi in odio. Nunc interdum lacus sit amet orci. Vestibulum rutrum, mi nec elementum vehicula, eros quam gravida nisl, id fringilla neque ante vel mi. Morbi mollis tellus ac sapien.</p>
                    </div>
                </div>

                <!-- Konten: Lapor Kerusakan -->
                <div x-show="activeTab === 'lapor'" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4" 
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="display: none;" x-cloak>
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Helpdesk & Lapor Kerusakan</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis. Curabitur a felis in nunc fringilla tristique. Morbi mattis ullamcorper velit. Phasellus gravida semper nisi.</p>
                        
                        <div class="p-6 bg-red-50 rounded-2xl border border-red-100 mt-8">
                            <h3 class="text-red-800 font-semibold mb-2">Prosedur Darurat</h3>
                            <p class="text-sm m-0 text-red-700">Nullam vel sem. Suspendisse eu ligula. Fusce vel dui. Vivamus in erat ut urna cursus vestibulum. Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci.</p>
                        </div>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Estimasi Penanganan</h2>
                        <p>Aenean ut eros et nisl sagittis vestibulum. Nullam nulla eros, ultricies sit amet, nonummy id, imperdiet feugiat, pede. Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-public-layout>
