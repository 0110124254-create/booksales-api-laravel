<div class="space-y-5 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
    @if ($errors->any())
        <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Periksa kembali data buku.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <label for="title" class="block text-sm font-bold text-stone-800">Judul buku</label>
        <input id="title" name="title" type="text" value="{{ old('title', $book->title ?? '') }}" required maxlength="255" class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-950 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10">
        @error('title') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="author_id" class="block text-sm font-bold text-stone-800">Penulis</label>
            <select id="author_id" name="author_id" required class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-950 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10">
                <option value="">Pilih penulis</option>
                @foreach ($authors as $author)
                    <option value="{{ $author->id }}" @selected((string) old('author_id', $book->author_id ?? '') === (string) $author->id)>{{ $author->name }}</option>
                @endforeach
            </select>
            @error('author_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="genre" class="block text-sm font-bold text-stone-800">Genre</label>
            <select id="genre" name="genre" required class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-950 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10">
                <option value="">Pilih genre</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre->name }}" @selected(old('genre', $book->genre ?? '') === $genre->name)>{{ $genre->name }}</option>
                @endforeach
            </select>
            @error('genre') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-bold text-stone-800">Deskripsi <span class="font-normal text-stone-500">(opsional)</span></label>
        <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-950 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10">{{ old('description', $book->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="price" class="block text-sm font-bold text-stone-800">Harga (rupiah)</label>
            <input id="price" name="price" type="number" min="0" step="1" value="{{ old('price', $book->price ?? '') }}" required class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-950 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10">
            @error('price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="stock" class="block text-sm font-bold text-stone-800">Jumlah stok</label>
            <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $book->stock ?? 0) }}" required class="mt-2 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-stone-950 outline-none transition focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10">
            @error('stock') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="rounded-2xl bg-stone-50 p-4">
        <input type="hidden" name="is_active" value="0">
        <label for="is_active" class="flex cursor-pointer items-center gap-3">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $book->is_active ?? true)) class="size-4 rounded border-stone-300 accent-emerald-900">
            <span>
                <span class="block text-sm font-bold text-stone-900">Tampilkan di katalog publik</span>
                <span class="mt-0.5 block text-xs text-stone-500">Matikan untuk menyembunyikan buku tanpa menghapusnya.</span>
            </span>
        </label>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-stone-100 pt-5">
        <button type="submit" class="rounded-full bg-emerald-950 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">{{ $submitLabel }}</button>
        <a href="{{ route('admin.web-books.index') }}" class="rounded-full border border-stone-300 px-6 py-3 text-sm font-bold text-stone-700 transition hover:border-stone-500">Batal</a>
    </div>
</div>
