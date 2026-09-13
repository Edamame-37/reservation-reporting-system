{{-- 
  NAMA FILE      : report-form.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Formulir Pelaporan Kerusakan Fasilitas (Mahasiswa / Dosen)
  DESKRIPSI      : Form pengaduan malfungsi atau kerusakan fisik fasilitas kampus dengan kategori kerusakan, deskripsi, dan upload foto bukti (Maks 5MB).
  CARA KERJA     : Memanfaatkan layout <x-app-layout active="report-form">, mengirimkan form multipart/form-data via POST ke /user/reports.
--}}

<x-app-layout title="Pelaporan Kerusakan Fasilitas" active="report-form">
    <!-- 
      ELEMEN       : Section Pelaporan Kerusakan (UR06, SFR07)
      KEGUNAAN     : Memungkinkan sivitas melaporkan sarana yang rusak/malfungsi di lapangan secara cepat dari desktop maupun mobile.
      CARA KERJA   : Alpine.js mengelola pemilihan kategori kerusakan dan visualisasi nama file yang diunggah.
    -->
    <section class="bg-surface-container-lowest rounded-xl shadow-md p-space-xl mb-space-xl" id="section-kerusakan" x-data="{
        selectedCategory: 'AC & Pendingin',
        fileName: '',
        setCategory(cat) {
            this.selectedCategory = cat;
        },
        handleFile(e) {
            if (e.target.files.length > 0) {
                this.fileName = e.target.files[0].name;
            }
        }
    }">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-space-md mb-space-lg border-b border-outline-variant gap-2">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-lg bg-error-container text-on-error-container flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[24px]">build</span>
                </div>
                <div>
                    <h1 class="font-headline-md text-headline-md text-primary">Pelaporan Kerusakan Fasilitas Kampus</h1>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Modul UR06 & SFR07: Adukan malfungsi fasilitas secara mandiri untuk percepatan maintenance sarpras.</p>
                </div>
            </div>
            <span class="font-data-mono text-data-mono bg-surface-container-low px-space-md py-1 rounded-lg text-primary text-[11px] font-bold">
                SLA PERBAIKAN: &lt; 24 JAM
            </span>
        </div>

        <!-- 
          ROUTE: Mengirimkan form multipart/form-data via POST ke /user/reports
          FUNGSI: Menyimpan tiket pelaporan kerusakan beserta foto bukti ke server
        -->
        <form action="{{ url('/user/reports') }}" method="POST" enctype="multipart/form-data" class="max-w-3xl mx-auto flex flex-col gap-space-lg">
            @csrf

            {{-- Fasilitas Bermasalah --}}
            <div class="flex flex-col gap-1">
                <label class="font-label-lg text-label-lg text-on-surface" for="rep-facility">Fasilitas / Ruangan Bermasalah</label>
                <select id="rep-facility" name="facility_id" class="w-full h-11 px-space-md bg-surface-container-low rounded-lg text-body-md text-on-surface border border-outline-variant/60 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    <option value="1">Auditorium B.J. Habibie - Gedung A</option>
                    <option value="2">Lab Jaringan & Cloud Komputasi - Gedung C Lt. 2</option>
                    <option value="3">Smart Classroom 302 - Gedung B Lt. 3</option>
                    <option value="4">Aula Kemahasiswaan & Olahraga - PKM</option>
                    <option value="5">Ruang Seminar Lantai 3 - Gedung A</option>
                </select>
            </div>

            {{-- Kategori Kerusakan Pill Selection --}}
            <div class="flex flex-col gap-1">
                <label class="font-label-lg text-label-lg text-on-surface">Kategori Kerusakan</label>
                <input type="hidden" name="category" :value="selectedCategory">
                <div class="flex flex-wrap gap-2 pt-1">
                    <template x-for="cat in ['AC & Pendingin', 'Kelistrikan / Stop Kontak', 'Proyektor & Audio', 'Mebel & Meja Kursi', 'Jaringan Internet']" :key="cat">
                        <button type="button" 
                            @click="setCategory(cat)"
                            :class="selectedCategory === cat ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface-container-low text-on-surface hover:bg-surface-container border border-outline-variant/40'"
                            class="px-space-md py-1.5 rounded-full font-label-md text-label-md transition-all">
                            <span x-text="cat"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Deskripsi Masalah Spesifik --}}
            <div class="flex flex-col gap-1">
                <label class="font-label-lg text-label-lg text-on-surface" for="rep-desc">Deskripsi Masalah Spesifik</label>
                <textarea id="rep-desc" name="description" rows="4" required placeholder="Jelaskan titik kerusakan, unit tertentu, atau gejala (misal: AC unit 2 bocor air, kabel proyektor HDMI putus)..." class="w-full p-space-md bg-surface-container-low rounded-lg font-body-md text-body-md text-on-surface border border-outline-variant/60 focus:border-primary focus:bg-surface-container-lowest focus:outline-none"></textarea>
            </div>

            {{-- Drag-and-drop Photo Upload Zone (SFR07) --}}
            <div class="flex flex-col gap-1">
                <label class="font-label-lg text-label-lg text-on-surface">Bukti Foto Kerusakan (Maks. 5MB, Format JPG/PNG)</label>
                <div class="relative p-space-xl bg-surface-container-low rounded-xl border-2 border-dashed border-outline-variant flex flex-col items-center justify-center text-center hover:bg-surface-container transition-colors">
                    <input type="file" name="evidence_photo" accept="image/*" @change="handleFile($event)" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <span class="material-symbols-outlined text-primary text-[42px] mb-2">cloud_upload</span>
                    <span class="font-label-lg text-label-lg text-primary font-semibold">Tarik & Letakkan Foto Kerusakan Di Sini</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">atau klik untuk memilih file dari galeri / kamera ponsel</span>
                    <div class="font-data-mono text-[11px] text-secondary font-bold mt-2" x-text="fileName ? 'File Terpilih: ' + fileName : 'Status: Belum ada file dipilih'"></div>
                </div>
            </div>

            {{-- Tombol Kirim Laporan --}}
            <div class="pt-space-md">
                <button type="submit" class="w-full py-3 px-space-lg bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg rounded-lg shadow-md transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">report</span>
                    <span>Kirim Laporan Kerusakan</span>
                </button>
            </div>
        </form>
    </section>
</x-app-layout>
