"use client";

import Link from "next/link";

export default function ScanPage() {
	return (
		<main style={{ maxWidth: 720, margin: "40px auto", padding: 16, fontFamily: "system-ui, sans-serif" }}>
			<h1 style={{ fontSize: 24, marginBottom: 12 }}>Pemindai QR (Placeholder)</h1>
			<p style={{ color: "#555", marginBottom: 16 }}>Nantinya halaman ini akan mengaktifkan kamera dan memindai QR, lalu memanggil API check-in dengan lokasi.</p>
			<Link href="/" style={{ padding: "10px 16px", background: "black", color: "white", borderRadius: 8, textDecoration: "none" }}>Kembali ke Home</Link>
		</main>
	);
}


