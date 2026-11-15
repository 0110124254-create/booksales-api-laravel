import { PageTitle } from '../components/Notice';

export default function AboutPage() {
    return <article className="mx-auto max-w-3xl rounded-[2rem] border border-stone-200 bg-white p-8 sm:p-12"><PageTitle eyebrow="Tentang kami">Ruang Baca, tempat cerita bertemu pembaca.</PageTitle><div className="space-y-5 leading-8 text-stone-700"><p>Ruang Baca adalah toko buku yang membantu pembaca menemukan cerita, pengetahuan, dan inspirasi baru.</p><p>Kami memilih koleksi dari penulis Indonesia dan berbagai genre untuk menemani perjalanan membaca Anda.</p><p>Jelajahi katalog, pesan buku favorit, dan pantau pesanan Anda langsung dari akun.</p></div></article>;
}
