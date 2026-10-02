{{-- Kolom bersama form alamat. Butuh: $address, $formRoute --}}
@php $returnTo = request('return_to'); @endphp

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

<form method="POST" action="{{ $formRoute }}" class="space-y-5">
    @csrf
    @if (isset($address->id)) @method('PUT') @endif
    @if ($returnTo) <input type="hidden" name="return_to" value="{{ $returnTo }}"> @endif

    <x-input name="label" label="Label alamat" maxlength="64" icon="home"
             :value="$address->label" placeholder="Contoh: Rumah, Kost, Rumah Nenek" />

    <x-input name="recipient_name" label="Nama penerima/penghubung" required maxlength="120" icon="user"
             :value="$address->recipient_name" placeholder="Siapa yang ditemui petugas?" />

    <x-input name="phone" type="tel" label="Nomor telepon yang bisa dihubungi" required inputmode="tel" icon="phone"
             :value="$address->phone" placeholder="08xx xxxx xxxx" />

    <x-textarea name="address_line" label="Alamat lengkap" required rows="3" maxlength="500"
                :value="$address->address_line"
                placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan" />

    <div class="grid sm:grid-cols-3 gap-5">
        <x-input name="city" label="Kota/Kabupaten" required maxlength="96" icon="map-pin"
                 :value="$address->city" placeholder="Klaten" />
        <x-input name="province" label="Provinsi" maxlength="96"
                 :value="$address->province" placeholder="Jawa Tengah" />
        <x-input name="postal_code" label="Kode pos" inputmode="numeric" maxlength="16"
                 :value="$address->postal_code" placeholder="57411" />
    </div>

    {{-- Titik lokasi di peta (gratis: GPS browser + OpenStreetMap, tanpa API key) --}}
    <div>
        <span class="block text-sm font-semibold text-stone-700 mb-1.5">
            Titik lokasi di peta <span class="font-normal text-stone-400">(opsional, membantu petugas menemukan rumah)</span>
        </span>
        <div class="flex flex-wrap items-center gap-2 mb-2.5">
            <button type="button" id="btn-geolocate"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700 active:scale-[0.98] transition min-h-11 disabled:opacity-50">
                <x-icon name="map-pin" class="w-4.5 h-4.5" />
                Gunakan lokasi saya
            </button>
            <a id="link-gmaps" href="#" target="_blank" rel="noopener"
               class="hidden text-sm font-bold text-brand-700 hover:text-brand-800 underline underline-offset-2">
                Buka di Google Maps
            </a>
        </div>
        <div id="address-map" class="rounded-2xl ring-1 ring-stone-300 overflow-hidden bg-stone-100" style="height: 260px; position: relative; z-index: 0;"></div>
        <p id="geo-status" class="mt-1.5 text-xs text-stone-400" role="status"></p>
        <input type="hidden" name="latitude" id="input-latitude" value="{{ $address->latitude }}">
        <input type="hidden" name="longitude" id="input-longitude" value="{{ $address->longitude }}">
    </div>

    <x-textarea name="notes" label="Catatan untuk petugas (opsional)" rows="2" maxlength="500"
                :value="$address->notes"
                placeholder="Contoh: gang sempit, rumah pagar hijau sebelah masjid, hubungi dulu sebelum datang." />

    <label class="flex items-center gap-3 cursor-pointer">
        <input type="hidden" name="is_primary" value="0">
        <input type="checkbox" name="is_primary" value="1" @checked((bool) $address->is_primary)
               class="w-5 h-5 rounded-lg border-stone-300 text-brand-600 focus:ring-brand-500 focus:ring-offset-0" />
        <span class="text-sm font-semibold text-stone-700">Jadikan alamat utama</span>
    </label>

    <div class="flex gap-3 pt-2">
        <x-button :href="$returnTo ?: route('akun.pasien.alamat.index', $patient)" variant="ghost" full class="justify-center">Batal</x-button>
        <x-button type="submit" full icon="check" class="sm:w-auto sm:flex-none">Simpan Alamat</x-button>
    </div>
</form>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var latInput = document.getElementById('input-latitude');
    var lngInput = document.getElementById('input-longitude');
    var statusEl = document.getElementById('geo-status');
    var gmapsLink = document.getElementById('link-gmaps');
    var geoBtn = document.getElementById('btn-geolocate');
    var mapEl = document.getElementById('address-map');
    if (!latInput || !mapEl) return;

    var DEFAULT_LAT = -7.702, DEFAULT_LNG = 110.602, DEFAULT_ZOOM = 13;
    var startLat = parseFloat(latInput.value);
    var startLng = parseFloat(lngInput.value);
    var hasStart = Number.isFinite(startLat) && Number.isFinite(startLng);
    var center = hasStart ? [startLat, startLng] : [DEFAULT_LAT, DEFAULT_LNG];

    function setStatus(msg) { if (statusEl) statusEl.textContent = msg; }
    function refreshGmapsLink() {
        var la = parseFloat(latInput.value), ln = parseFloat(lngInput.value);
        if (Number.isFinite(la) && Number.isFinite(ln)) {
            gmapsLink.href = 'https://www.google.com/maps?q=' + la + ',' + ln;
            gmapsLink.classList.remove('hidden');
        } else {
            gmapsLink.classList.add('hidden');
        }
    }
    function setCoords(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        refreshGmapsLink();
    }
    function setIfEmpty(id, val) {
        if (!val) return;
        var el = document.getElementById(id);
        if (el && !el.value.trim()) el.value = val;
    }
    async function reverseGeocode(lat, lng) {
        try {
            var r = await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1&accept-language=id');
            if (r.ok) {
                var j = await r.json();
                if (j && j.address) {
                    var a = j.address;
                    var street = [a.road, a.house_number].filter(Boolean).join(' ');
                    var area = a.suburb || a.village || a.hamlet || a.neighbourhood || a.quarter || '';
                    var line = [street, area].filter(Boolean).join(', ') || (j.display_name || '').split(',').slice(0, 2).join(',');
                    return {
                        line: line,
                        city: a.city || a.town || a.village || a.municipality || a.county || a.state_district || '',
                        province: a.state || '',
                        postcode: a.postcode || ''
                    };
                }
            }
        } catch (e) { /* lanjut ke fallback */ }
        try {
            var r2 = await fetch('https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + lat + '&longitude=' + lng + '&localityLanguage=id');
            if (r2.ok) {
                var j2 = await r2.json();
                if (j2) return {
                    line: '',
                    city: j2.city || j2.locality || '',
                    province: j2.principalSubdivision || '',
                    postcode: j2.postcode || ''
                };
            }
        } catch (e2) { /* abaikan */ }
        return null;
    }
    async function applyReverse(lat, lng) {
        setStatus('Mencari nama alamat…');
        var info = await reverseGeocode(lat, lng);
        if (info) {
            setIfEmpty('address_line', info.line);
            setIfEmpty('city', info.city);
            setIfEmpty('province', info.province);
            setIfEmpty('postal_code', info.postcode);
            setStatus('Koordinat tersimpan. Kolom yang masih kosong terisi otomatis — silakan periksa kembali.');
        } else {
            setStatus('Koordinat tersimpan, tetapi nama alamat tidak ditemukan otomatis. Silakan isi manual.');
        }
    }

    var map = null, marker = null;
    if (typeof L !== 'undefined') {
        map = L.map('address-map').setView(center, hasStart ? 16 : DEFAULT_ZOOM);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        marker = L.marker(center, { draggable: true }).addTo(map);
        marker.on('dragend', function () {
            var p = marker.getLatLng();
            setCoords(p.lat, p.lng);
            applyReverse(p.lat, p.lng);
        });
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            setCoords(e.latlng.lat, e.latlng.lng);
            applyReverse(e.latlng.lat, e.latlng.lng);
        });
    } else {
        setStatus('Peta tidak dapat dimuat (periksa koneksi). Tombol lokasi di atas tetap bisa dipakai.');
    }

    refreshGmapsLink();

    geoBtn.addEventListener('click', function () {
        if (!('geolocation' in navigator)) { setStatus('Browser tidak mendukung GPS.'); return; }
        geoBtn.disabled = true;
        setStatus('Meminta lokasi…');
        navigator.geolocation.getCurrentPosition(function (pos) {
            geoBtn.disabled = false;
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            setCoords(lat, lng);
            if (map) { map.setView([lat, lng], 16); marker.setLatLng([lat, lng]); }
            applyReverse(lat, lng);
        }, function (err) {
            geoBtn.disabled = false;
            setStatus(err && err.code === 1
                ? 'Izin lokasi ditolak. Izinkan akses lokasi di browser lalu coba lagi.'
                : 'Gagal mendapatkan lokasi. Coba lagi atau geser pin manual di peta.');
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 });
    });
})();
</script>
@endpush
