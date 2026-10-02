@props(['patient', 'selectable' => false, 'selected' => false, 'showActions' => true])

@if ($selectable)
    <label class="block cursor-pointer">
        <input type="radio" name="patient_profile_id" value="{{ $patient->id }}"
               @checked(old('patient_profile_id', $selected))
               class="peer sr-only" required />
        <div class="rounded-2xl ring-1 bg-white p-4 sm:p-5 transition peer-checked:ring-2 peer-checked:ring-brand-600 peer-checked:bg-brand-50/50 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-600 hover:ring-brand-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg shrink-0">
                    {{ mb_substr($patient->name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="choice-name font-bold text-stone-900 truncate">{{ $patient->name }}</p>
                    <p class="text-sm text-stone-500">
                        {{ \App\Http\Requests\Patient\StorePatientProfileRequest::relationshipChoices() && in_array($patient->relationship, \App\Http\Requests\Patient\StorePatientProfileRequest::relationshipChoices()) ? ucfirst(str_replace('_', ' ', $patient->relationship)) : 'Pasien' }}
                        @if ($patient->age()) · {{ $patient->age() }} tahun @endif
                        @if ($patient->gender) · {{ $patient->gender->label() }} @endif
                    </p>
                </div>
                <span class="choice-dot w-6 h-6 rounded-full border-2 border-stone-300 flex items-center justify-center shrink-0 transition
                    {{ $selected || old('patient_profile_id') == $patient->id ? 'border-brand-600 bg-brand-600' : '' }}">
                    <x-icon name="check" class="w-3.5 h-3.5 text-white" />
                </span>
            </div>
        </div>
    </label>
@else
    <div class="rounded-2xl ring-1 ring-stone-200/70 bg-white p-4 sm:p-5 shadow-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg shrink-0">
                {{ mb_substr($patient->name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-stone-900 truncate">{{ $patient->name }}</p>
                <p class="text-sm text-stone-500">
                    {{ $patient->relationship ? ucfirst(str_replace('_', ' ', $patient->relationship)) : 'Pasien' }}
                    @if ($patient->age()) · {{ $patient->age() }} tahun @endif
                    @if ($patient->gender) · {{ $patient->gender->label() }} @endif
                </p>
            </div>
            @if ($showActions)
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('akun.pasien.edit', $patient) }}" class="rounded-xl p-2.5 text-stone-400 hover:bg-stone-100 hover:text-brand-700 transition" aria-label="Ubah data {{ $patient->name }}">
                        <x-icon name="edit" class="w-5 h-5" />
                    </a>
                </div>
            @endif
        </div>

        @if ($patient->addresses->isNotEmpty())
            <p class="mt-3 text-sm text-stone-500 flex items-start gap-1.5">
                <x-icon name="map-pin" class="w-4 h-4 mt-0.5 shrink-0 text-stone-400" />
                <span class="line-clamp-1">{{ $patient->primaryAddress()?->oneLine() }}</span>
            </p>
        @endif
    </div>
@endif
