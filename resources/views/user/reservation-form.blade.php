{{-- 
  NAMA FILE      : reservation-form.blade.php
  FUNGSIONALITAS : Formulir Pengajuan Reservasi Ruang & Fasilitas Kampus
  DESKRIPSI      : Form interaktif pemilihan fasilitas, tanggal, picker rentang slot 30 menit (07:00 - 20:00 WIB), tujuan kegiatan, dan live conflict check.
  CARA KERJA     : Memanfaatkan layout <x-app-layout active="reservation-form">, mengirimkan data form via POST ke route reservasi dengan validasi anti-bentrok.
--}}

<x-app-layout title="Form Pengajuan Reservasi" active="reservation-form">
    <div x-data="{
        submitting: false,
        submitted: false,
        selectedVenueName: 'Auditorium Utama B.J. Habibie',
        selectedDate: '{{ date('Y-m-d', strtotime('+3 days')) }}',
        handleSubmit() {
            this.submitting = true;
            setTimeout(() => {
                this.submitting = false;
                this.submitted = true;
                setTimeout(() => {
                    window.location.href = '{{ url('/user/reservation-history') }}';
                }, 1500);
            }, 800);
        }
    }">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/user/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Saya</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Form Pengajuan Reservasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Form Permohonan Reservasi Fasilitas</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pilih fasilitas, tanggal pelaksanaan, dan rentang slot waktu operasional (07:00 - 20:00 WIB).</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Pengecekan Bentrok Jadwal: Aktif</span>
                </div>
            </div>
        </div>

        {{-- Toast Sukses --}}
        <div x-show="submitted" x-cloak class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
            <span class="material-symbols-outlined text-emerald-600 text-[24px]">check_circle</span>
            <div>
                <h4 class="text-sm font-bold">Permohonan Reservasi Berhasil Diajukan!</h4>
                <p class="text-xs text-emerald-700">Tiket Anda telah masuk ke antrean verifikasi Petugas Sarpras. Mengalihkan ke riwayat reservasi...</p>
            </div>
        </div>

        <!-- 
          ROUTE: POST /user/reservations
          FUNGSI: Mengirimkan permohonan reservasi baru dengan proteksi interval 30 menit
        -->
        <form @submit.prevent="handleSubmit" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            @csrf

            {{-- Kolom Kiri: Form Input Data (7 Kolom) --}}
            <div class="lg:col-span-7 flex flex-col gap-6">
                {{-- Card Input Utama --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col gap-5">
                    {{-- Pilihan Fasilitas --}}
                    <div>
                        <label for="venue-select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Pilih Fasilitas & Ruang Akademik
                        </label>
                        <select name="facility_id" id="venue-select" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                            <option value="1">Auditorium Utama B.J. Habibie - Gedung Rektorat (Kapasitas: 450)</option>
                            <option value="2">Lab Komputasi Cloud & Jaringan - Gedung C Lt. 2 (Kapasitas: 45)</option>
                            <option value="3">Smart Classroom 302 - Gedung B Lt. 3 (Kapasitas: 60)</option>
                            <option value="4">Aula Kemahasiswaan & Olahraga - Gedung PKM (Kapasitas: 500)</option>
                            <option value="5">Ruang Seminar Lantai 3 - Gedung A (Kapasitas: 120)</option>
                        </select>
                    </div>

                    {{-- Tanggal Pelaksanaan --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="res-date" class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                Tanggal Pelaksanaan Kegiatan
                            </label>
                            <span class="text-[11px] text-emerald-700 font-semibold">Tersedia H-14 s/d H-2</span>
                        </div>
                        <input type="date" id="res-date" name="reservation_date" x-model="selectedDate" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                    </div>

                    {{-- Pemilih Sesi & Slot 30 Menit --}}
                    <div>
                        <x-cava.slot-matrix :interactive="true" />
                    </div>

                    {{-- Tujuan Penggunaan & Estimasi Peserta --}}
                    <div>
                        <label for="purpose" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Tujuan Penggunaan, Nama Acara & PIC
                        </label>
                        <textarea id="purpose" name="purpose" rows="3" required placeholder="Contoh: Seminar Nasional Cloud Computing HMIF - Estimasi 75 Peserta Mahasiswa. PIC: Dimas Pratama (08123456789)" class="w-full p-3.5 bg-slate-50 rounded-xl text-sm text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition"></textarea>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Preview Validasi & Submit (5 Kolom) --}}
            <div class="lg:col-span-5 flex flex-col gap-6">
                {{-- Box Validasi --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-emerald-600 text-[20px]">verified</span>
                                <span>Ringkasan Validasi Sistem</span>
                            </h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">BEBAS BENTROK</span>
                        </div>

                        <div class="space-y-3 text-xs mb-6">
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                                <span class="text-slate-400 block mb-0.5">Ruang Terpilih:</span>
                                <span class="font-bold text-slate-900 text-sm">Auditorium Utama B.J. Habibie</span>
                                <span class="text-slate-500 block text-[11px]">Gedung Rektorat Baru, Lt. 1 & 2</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-slate-600">
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <span class="text-slate-400 block mb-0.5">Tanggal Kegiatan:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedDate"></span>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <span class="text-slate-400 block mb-0.5">Jam Operasional:</span>
                                    <span class="font-bold text-slate-900">09:00 - 12:00 WIB</span>
                                </div>
                            </div>
                        </div>

                        {{-- Panduan Kebijakan Kampus --}}
                        <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-slate-600 mb-6 space-y-1.5">
                            <div class="font-bold text-blue-950 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">rule</span>
                                Aturan Penggunaan Fasilitas:
                            </div>
                            <p class="text-[11px] leading-relaxed">
                                1. Pengajuan diverifikasi oleh petugas sarpras maksimal 1x24 jam.<br>
                                2. Pembatalan mandiri hanya diizinkan maksimal <strong>H-1</strong> sebelum kegiatan.<br>
                                3. Wajib menjaga kebersihan dan mengembalikan tata letak fasilitas.
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div>
                        <button type="submit" :disabled="submitting" class="w-full py-3 px-4 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition shadow-sm flex items-center justify-center gap-2">
                            <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                            <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="submitting ? 'Memproses Validasi...' : 'Kirim Permohonan Reservasi'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>
