{{-- 
  NAMA FILE      : report-form.blade.php
  FUNGSIONALITAS : Formulir Pelaporan Kerusakan & Malfungsi Fasilitas Kampus (USR-04)
  DESKRIPSI      : Form pengaduan malfungsi atau kerusakan fisik fasilitas kampus dengan dropdown dinamis, kategori kerusakan, deskripsi masalah, dan pratinjau unggah foto bukti (Maks 2MB).
  DESAIN         : Opsi 1 (Apple-esque Bento) dengan styling borderless soft gray (bg-[#F5F5F7] border-0), tipografi bersih Outfit/Inter, tanpa AI slop / tanpa border hitam pekat.
  CARA KERJA     : Menerima koleksi $facilities dari ReportController@create, mengirimkan multipart POST ke user.reports.store, divalidasi via StoreDamageReportRequest.
--}}

<x-app-layout title="Form Lapor Kerusakan Fasilitas" active="report-form">
    @php
        $categories = [
            'AC & Pendingin',
            'Kelistrikan / Stop Kontak',
            'Proyektor & Audio',
            'Mebel & Meja Kursi',
            'Jaringan & Kabel',
            'Fisik Bangunan / Pintu / Jendela',
            'Kebersihan',
            'Lainnya'
        ];
    @endphp

    <div x-data="{
        submitting: false,
        selectedCategory: '{{ old('category', '') }}',
        fileName: '',
        imagePreview: null,
        fileError: '',

        handleFile(e) {
            const file = e.target.files[0];
            this.fileError = '';
            if (file) {
                // Validasi ukuran file di sisi browser (Maksimal 2 MB / 2048 KB)
                if (file.size > 2 * 1024 * 1024) {
                    this.fileError = 'Ukuran berkas melebihi batas maksimal 2 MB (terbaca: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Silakan gunakan gambar yang lebih kecil.';
                    e.target.value = '';
                    this.fileName = '';
                    this.imagePreview = null;
                    return;
                }
                // Validasi tipe file
                if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                    this.fileError = 'Format berkas tidak didukung. Harap pilih gambar bertipe JPG atau PNG.';
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

        resetFile() {
            this.imagePreview = null;
            this.fileName = '';
            this.fileError = '';
            const input = document.getElementById('photo-file');
            if (input) input.value = '';
        }
    }" class="max-w-4xl mx-auto space-y-8">

        {{-- Top Navigation: Link Kembali & Header Halaman Apple-Style --}}
        <div>
            <a href="{{ route('user.reports.index') }}" class="inline-flex items-center gap-1.5 text-sm text-[#86868B] hover:text-[#1D1D1F] font-medium transition mb-4">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Kembali ke Riwayat Laporan</span>
            </a>
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#1D1D1F]">Formulir Pelaporan Kerusakan</h1>
                    <p class="text-base sm:text-lg text-[#86868B] font-light">Sampaikan kendala kerusakan sarana prasarana agar segera ditangani tim teknisi kampus.</p>
                </div>
                <div class="shrink-0">
                    <span class="text-xs font-semibold tracking-wide text-emerald-800">
                        Respon Petugas &lt; 24 Jam Kerja
                    </span>
                </div>
            </div>
        </div>

        {{-- Flash Session Sukses --}}
        @if (session('success'))
            <div class="p-6 rounded-[2rem] bg-emerald-50/70 border border-emerald-100 text-emerald-900 flex items-center gap-4">
                <span class="material-symbols-outlined text-emerald-600 text-[24px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Laporan Berhasil Terkirim!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash Session Error / Validation Errors --}}
        @if ($errors->any())
            <div class="p-6 rounded-[2rem] bg-rose-50/70 border border-rose-100 text-rose-900 flex items-start gap-4">
                <span class="material-symbols-outlined text-rose-600 text-[24px] shrink-0 mt-0.5">error</span>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold">Terdapat Kesalahan pada Formulir!</h4>
                    <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- 
          ROUTE: POST /user/reports
          FUNGSI: Mengirimkan data kerusakan sarpras beserta foto bukti (JPG/PNG < 2MB)
        -->
        <form action="{{ route('user.reports.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              @submit="submitting = true" 
              class="bg-white rounded-[2rem] p-8 md:p-12 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-8">
            @csrf

            <div class="space-y-6">
                {{-- 1. Fasilitas Bermasalah --}}
                <div>
                    <label for="rep-facility" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                        Fasilitas / Ruangan yang Mengalami Kerusakan <span class="text-rose-500">*</span>
                    </label>
                    <select id="rep-facility" 
                            name="facility_id" 
                            required 
                            class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3.5 rounded-xl text-sm {{ $errors->has('facility_id') ? 'ring-2 ring-rose-400 bg-rose-50/50' : '' }} focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                        <option value="">-- Pilih Fasilitas / Ruangan Kampus --</option>
                        @if (isset($facilities))
                            @foreach ($facilities as $facility)
                                <option value="{{ $facility->id }}" {{ old('facility_id', $selectedFacilityId ?? '') == $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }} - {{ $facility->building ?? 'Kampus' }} (Kapasitas {{ $facility->capacity ?? '-' }} Orang)
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('facility_id')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2. Kategori Kerusakan --}}
                <div>
                    <label for="rep-category" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                        Kategori Kerusakan Sarana
                    </label>
                    <select id="rep-category" 
                            name="category" 
                            class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                        <option value="">-- Pilih Kategori Kendala (Opsional) --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-[#86868B]">
                        Pilih kategori yang paling sesuai atau dapat dikosongkan dan diuraikan pada deskripsi masalah.
                    </p>
                    @error('category')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 3. Deskripsi Masalah (Wajib) --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="rep-desc" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider">
                            Deskripsi Rinci Kendala / Lokasi Spesifik <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-xs text-[#86868B]">Minimal 10 karakter</span>
                    </div>
                    <textarea id="rep-desc" 
                              name="description" 
                              rows="5" 
                              required 
                              minlength="10" 
                              placeholder="Jelaskan secara spesifik kerusakan yang terjadi, misalnya: 'Unit AC di dekat pintu keluar mengeluarkan suara bising dan proyektor tidak merespon saat disambungkan ke kabel HDMI di meja dosen...'" 
                              class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] p-4 rounded-xl text-sm {{ $errors->has('description') ? 'ring-2 ring-rose-400 bg-rose-50/50' : '' }} focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition leading-relaxed">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 4. Upload Foto Bukti --}}
                <div>
                    <label class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                        Unggah Foto Bukti Kerusakan <span class="text-rose-500">*</span>
                        <span class="text-[#86868B] font-normal lowercase">(Maks. 2 MB - JPG/PNG)</span>
                    </label>
                    
                    {{-- Banner Error Klien --}}
                    <div x-show="fileError" x-cloak class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-800 text-xs flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px] text-rose-600">warning</span>
                        <span x-text="fileError"></span>
                    </div>

                    <div class="border border-dashed border-gray-200 bg-[#F9F9FB] rounded-2xl p-8 text-center hover:bg-gray-50 transition relative">
                        <input type="file" 
                               id="photo-file" 
                               name="attachment_photo" 
                               accept="image/png, image/jpeg, image/jpg" 
                               required 
                               @change="handleFile" 
                               class="hidden">
                        
                        <template x-if="!imagePreview">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-[#86868B] text-[40px] mb-2 font-light">add_photo_alternate</span>
                                <div class="text-sm font-medium text-[#1D1D1F]">Pilih foto bukti dari perangkat Anda</div>
                                <div class="text-xs text-[#86868B] mt-0.5">Format file JPG atau PNG hingga ukuran 2 MB</div>
                                <label for="photo-file" class="mt-4 px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-medium text-[#1D1D1F] hover:bg-gray-50 shadow-xs cursor-pointer transition">
                                    Telusuri Foto
                                </label>
                            </div>
                        </template>

                        <template x-if="imagePreview">
                            <div class="flex flex-col items-center">
                                <img :src="imagePreview" alt="Pratinjau Foto" class="h-48 w-auto object-cover rounded-2xl border border-gray-100 mb-3 shadow-xs">
                                <div class="text-xs font-mono font-medium text-[#1D1D1F] truncate max-w-xs" x-text="fileName"></div>
                                <button type="button" @click="resetFile()" class="mt-2 text-xs font-medium text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">delete</span>
                                    <span>Hapus & Ganti Foto</span>
                                </button>
                            </div>
                        </template>
                    </div>
                    @error('attachment_photo')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Tombol Aksi Formulir --}}
            <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('user.reports.index') }}" class="px-5 py-3 text-sm font-medium text-[#86868B] hover:text-[#1D1D1F] transition">
                    Batal
                </a>
                <button type="submit" 
                        :disabled="submitting" 
                        class="px-8 py-3.5 rounded-xl bg-[#1D1D1F] text-white text-sm font-medium hover:bg-black disabled:opacity-50 transition shadow-sm flex items-center gap-2">
                    <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                    <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="submitting ? 'Mengirim Laporan...' : 'Kirim Laporan Kerusakan'"></span>
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
