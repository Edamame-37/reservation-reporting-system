{{-- 
  NAMA FILE      : report-form.blade.php
  FUNGSIONALITAS : Formulir Pelaporan Kerusakan & Malfungsi Fasilitas Kampus
  DESKRIPSI      : Form pengaduan malfungsi atau kerusakan fisik fasilitas kampus dengan kategori kerusakan, deskripsi masalah, dan pratinjau unggah foto bukti (Maks 2MB).
  CARA KERJA     : Menggunakan layout <x-app-layout active="report-form">, memproses input form interaktif via Alpine.js dengan validasi ukuran file.
--}}

<x-app-layout title="Form Lapor Kerusakan Fasilitas" active="report-form">
    <div x-data="{
        submitting: false,
        submitted: false,
        selectedCategory: 'AC & Pendingin',
        fileName: '',
        imagePreview: null,
        handleFile(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file melebihi batas maksimal 2 MB sesuai aturan standarisasi media.');
                    e.target.value = '';
                    this.fileName = '';
                    this.imagePreview = null;
                    return;
                }
                this.fileName = file.name;
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.imagePreview = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        handleSubmit() {
            this.submitting = true;
            setTimeout(() => {
                this.submitting = false;
                this.submitted = true;
                setTimeout(() => {
                    window.location.href = '{{ url('/user/report-history') }}';
                }, 1500);
            }, 800);
        }
    }">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/user/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Saya</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Form Lapor Kerusakan</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pelaporan Kerusakan Sarana & Fasilitas</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Sampaikan kendala fasilitas rusak atau malfungsi agar segera ditindaklanjuti oleh staf sarpras.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px] text-amber-600">timer</span>
                    <span>Target Respon SLA: &lt; 24 Jam</span>
                </div>
            </div>
        </div>

        {{-- Toast Sukses --}}
        <div x-show="submitted" x-cloak class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
            <span class="material-symbols-outlined text-emerald-600 text-[24px]">check_circle</span>
            <div>
                <h4 class="text-sm font-bold">Laporan Kerusakan Berhasil Dikirim!</h4>
                <p class="text-xs text-emerald-700">Tiket perbaikan telah dibuat dan diteruskan ke petugas sarpras zona terkait. Mengalihkan...</p>
            </div>
        </div>

        <!-- 
          ROUTE: POST /user/reports
          FUNGSI: Mengirimkan data kerusakan sarpras beserta foto bukti (JPG/PNG < 2MB)
        -->
        <form @submit.prevent="handleSubmit" class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs flex flex-col gap-6">
            @csrf

            {{-- Fasilitas Bermasalah --}}
            <div>
                <label for="rep-facility" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Fasilitas / Ruangan yang Mengalami Kerusakan
                </label>
                <select id="rep-facility" name="facility_id" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                    <option value="1">Auditorium Utama B.J. Habibie - Gedung Rektorat Lt. 1</option>
                    <option value="2">Lab Komputasi Cloud & Jaringan - Gedung C Lt. 2</option>
                    <option value="3">Smart Classroom 302 - Gedung Kuliah Bersama B Lt. 3</option>
                    <option value="4">Aula Kemahasiswaan & Olahraga - Gedung PKM</option>
                    <option value="5">Ruang Seminar Lantai 3 - Gedung A</option>
                </select>
            </div>

            {{-- Kategori Kerusakan --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Kategori Kerusakan Sarana
                </label>
                <div class="flex flex-wrap gap-2">
                    <template x-for="cat in ['AC & Pendingin', 'Kelistrikan / Stop Kontak', 'Proyektor & Audio', 'Mebel & Meja Kursi', 'Jaringan & Kabel']" :key="cat">
                        <button type="button" 
                                @click="selectedCategory = cat"
                                :class="selectedCategory === cat ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200/60'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition">
                            <span x-text="cat"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Deskripsi Masalah --}}
            <div>
                <label for="rep-desc" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Deskripsi Masalah / Lokasi Spesifik
                </label>
                <textarea id="rep-desc" name="description" rows="4" required placeholder="Contoh: Kabel jaringan switch di meja baris 3 putus, atau proyektor mati mendadak saat perkuliahan..." class="w-full p-3.5 bg-slate-50 rounded-xl text-sm text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition"></textarea>
            </div>

            {{-- Upload Foto Bukti --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Unggah Foto Bukti Kerusakan (Maks. 2 MB - JPG/PNG)
                </label>
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-slate-400 transition bg-slate-50/50">
                    <template x-if="!imagePreview">
                        <div class="flex flex-col items-center">
                            <span class="material-symbols-outlined text-slate-400 text-[36px] mb-2">add_photo_alternate</span>
                            <span class="text-xs font-semibold text-slate-700">Pilih foto dari perangkat</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Format JPG atau PNG hingga 2 MB</span>
                            <label for="photo-file" class="mt-3 px-4 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-xs cursor-pointer">
                                Telusuri File
                            </label>
                            <input type="file" id="photo-file" accept="image/png, image/jpeg" @change="handleFile" class="hidden">
                        </div>
                    </template>

                    <template x-if="imagePreview">
                        <div class="flex flex-col items-center">
                            <img :src="imagePreview" alt="Pratinjau Foto" class="h-36 w-auto object-cover rounded-xl border border-slate-200 mb-2 shadow-xs">
                            <span class="text-xs font-mono text-slate-600" x-text="fileName"></span>
                            <button type="button" @click="imagePreview = null; fileName = ''" class="mt-2 text-xs font-semibold text-rose-600 hover:underline">
                                Hapus & Ganti Foto
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ url('/user/dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-2">
                    <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                    <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="submitting ? 'Mengirim...' : 'Kirim Laporan Kerusakan'"></span>
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
