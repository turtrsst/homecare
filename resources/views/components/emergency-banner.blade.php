@props(['compact' => false])

{{-- Pengingat tetap: homecare BUKAN layanan kegawatdaruratan --}}
<div {{ $attributes->merge(['class' => 'rounded-2xl bg-rose-50 ring-1 ring-rose-200 p-4 sm:p-5']) }} role="note">
    <div class="flex gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <x-icon name="ambulance" class="w-6 h-6" />
        </div>
        <div class="text-sm sm:text-base text-rose-900">
            <p class="font-bold">Kondisi darurat? Jangan gunakan layanan ini.</p>
            <p class="mt-0.5 text-rose-800">
                Homecare bukan layanan kegawatdaruratan. Segera hubungi
                <a href="tel:{{ config('homecare.emergency_number') }}" class="font-extrabold underline decoration-2 underline-offset-2">{{ config('homecare.emergency_number') }}</a>
                atau IGD {{ config('homecare.hospital_name') }}:
                <a href="tel:{{ config('homecare.contact.phone') }}" class="font-bold underline underline-offset-2">{{ config('homecare.contact.phone') }}</a>.
            </p>
        </div>
    </div>
</div>
