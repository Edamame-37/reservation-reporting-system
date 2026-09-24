{{-- 
  NAMA FILE      : report-form.blade.php
  FUNGSIONALITAS : Formulir Pelaporan Kerusakan & Malfungsi Fasilitas Kampus (USR-04)
  DESKRIPSI      : Form pengaduan malfungsi atau kerusakan fisik fasilitas kampus dengan dropdown dinamis, kategori kerusakan, deskripsi masalah, dan pratinjau unggah foto bukti (Maks 2MB).
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
        selectCategory(cat) {
            if (this.selectedCategory === cat) {
                this.selectedCategory = '';
            } else {
                this.selectedCategory = cat;
            }
        },
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

        {{-- Flash Session Sukses --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Laporan Berhasil Terkirim!</h4>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash Session Error / Validation Errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-rose-600 text-[24px]">error</span>
                <div>
                    <h4 class="text-sm font-bold">Terdapat Kesalahan pada Formulir!</h4>
                    <ul class="list-disc list-inside text-xs text-rose-700 mt-1 space-y-0.5">
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
              class="max-w-2xl bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs flex flex-col gap-6">
            @csrf

            {{-- Fasilitas Bermasalah --}}
            <div>
                <label for="rep-facility" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Fasilitas / Ruangan yang Mengalami Kerusakan <span class="text-rose-500">*</span>
                </label>
                <select id="rep-facility" 
                        name="facility_id" 
                        required 
                        class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border {{ $errors->has('facility_id') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200' }} focus:border-slate-900 focus:bg-white focus:outline-none transition">
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
                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Kategori Kerusakan (Opsional) --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Kategori Kerusakan Sarana
                    </label>
                    <span class="text-[11px] text-slate-500 font-medium">Opsional (dapat dilewati)</span>
                </div>
                <input type="hidden" name="category" :value="selectedCategory">

                <div class="flex flex-wrap gap-2">
                    @foreach ($categories as $cat)
                        <button type="button" 
                                @click="selectCategory('{{ $cat }}')"
                                :class="selectedCategory === '{{ $cat }}' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200/60'"
                                class="px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                            <span x-show="selectedCategory === '{{ $cat }}'" class="material-symbols-outlined text-[14px]">check</span>
                            <span>{{ $cat }}</span>
                        </button>
                    @endforeach
                </div>
                <p class="mt-1.5 text-[11px] text-slate-400">
                    Jika jenis kerusakan tidak ada pada opsi di atas, Anda dapat melewatinya dan menguraikannya pada deskripsi di bawah.
                </p>
                @error('category')
                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Deskripsi Masalah (Wajib) --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="rep-desc" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Deskripsi Masalah / Lokasi Spesifik <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] text-rose-600 font-semibold">Wajib Diisi (Min. 10 karakter)</span>
                </div>
                <textarea id="rep-desc" 
                          name="description" 
                          rows="4" 
                          required 
                          minlength="10"
                          placeholder="Jelaskan secara spesifik kerusakan yang terjadi, misalnya: 'Kabel proyektor HDMI putus dan remote AC tidak merespon saat dinyalakan di baris depan...'" 
                          class="w-full p-3.5 bg-slate-50 rounded-xl text-sm text-slate-800 border {{ $errors->has('description') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200' }} focus:border-slate-900 focus:bg-white focus:outline-none transition">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Upload Foto Bukti --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Unggah Foto Bukti Kerusakan <span class="text-rose-500">*</span>
                    <span class="text-slate-400 font-normal lowercase">(Maks. 2 MB - JPG/PNG)</span>
                </label>
                
                {{-- Banner Error Klien --}}
                <div x-show="fileError" x-cloak class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-rose-600">warning</span>
                    <span x-text="fileError"></span>
                </div>

                <div class="border-2 border-dashed {{ $errors->has('attachment_photo') ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200 bg-slate-50/50' }} rounded-2xl p-6 text-center hover:border-slate-400 transition">
                    <template x-if="!imagePreview">
                        <div class="flex flex-col items-center">
                            <span class="material-symbols-outlined text-slate-400 text-[36px] mb-2">add_photo_alternate</span>
                            <span class="text-xs font-semibold text-slate-700">Pilih foto bukti dari perangkat</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Format file JPG atau PNG hingga ukuran 2 MB</span>
                            <label for="photo-file" class="mt-3 px-4 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-xs cursor-pointer transition">
                                Telusuri Berkas
                            </label>
                            <input type="file" 
                                   id="photo-file" 
                                   name="attachment_photo" 
                                   accept="image/png, image/jpeg, image/jpg" 
                                   required
                                   @change="handleFile" 
                                   class="hidden">
                        </div>
                    </template>

                    <template x-if="imagePreview">
                        <div class="flex flex-col items-center">
                            <img :src="imagePreview" alt="Pratinjau Foto" class="h-40 w-auto object-cover rounded-xl border border-slate-200 mb-2 shadow-xs">
                            <span class="text-xs font-mono font-medium text-slate-700" x-text="fileName"></span>
                            <button type="button" @click="resetFile()" class="mt-2 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">delete</span>
                                <span>Hapus & Ganti Foto</span>
                            </button>
                        </div>
                    </template>
                </div>
                @error('attachment_photo')
                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ url('/user/dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        :disabled="submitting" 
                        class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 disabled:opacity-50 transition shadow-xs flex items-center gap-2">
                    <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                    <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="submitting ? 'Mengirim Laporan...' : 'Kirim Laporan Kerusakan'"></span>
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
