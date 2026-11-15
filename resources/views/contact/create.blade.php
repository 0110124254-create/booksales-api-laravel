@extends('layouts.catalog')

@section('title', 'Hubungi Admin')

@section('content')
<section class="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-20">
    <div class="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm sm:p-10">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-800">Kami siap membantu</p>
        <h1 class="mt-3 text-4xl font-black text-stone-950">Hubungi admin</h1>
        <p class="mt-3 text-stone-600">Kirim pertanyaan atau masukan. Pesan Anda akan tersimpan dan dapat dibaca admin.</p>
        @if ($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('contact.store') }}" class="mt-7 space-y-5">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div><label for="name" class="text-sm font-bold">Nama</label><input id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3"></div>
                <div><label for="email" class="text-sm font-bold">Email</label><input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3"></div>
            </div>
            <div><label for="subject" class="text-sm font-bold">Subjek</label><input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3"></div>
            <div><label for="message" class="text-sm font-bold">Pesan</label><textarea id="message" name="message" rows="6" required maxlength="5000" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3">{{ old('message') }}</textarea></div>
            <button class="rounded-full bg-violet-950 px-6 py-3 font-bold text-white hover:bg-violet-800">Kirim pesan</button>
        </form>
    </div>
</section>
@endsection
