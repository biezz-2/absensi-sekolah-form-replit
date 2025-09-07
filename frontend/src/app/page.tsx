"use client";


import Link from "next/link";
import { useEffect, useState } from "react";
import AdminStatistics from "./components/AdminStatistics";

export default function Home() {
	const [health, setHealth] = useState<string>("checking...");
	useEffect(() => {
		fetch("/api/health")
			.then(async (res) => {
				if (!res.ok) throw new Error("health failed");
				const j = await res.json();
				setHealth(`status: ${j.status}, app: ${j.app}`);
			})
			.catch(() => setHealth("unreachable"));
	}, []);

	return (
		<main style={{ maxWidth: 720, margin: "40px auto", padding: 16, fontFamily: "system-ui, sans-serif" }}>
			<h1 style={{ fontSize: 28, marginBottom: 8 }}>Sistem Absensi QR Siswa</h1>
			<p style={{ color: "#555", marginBottom: 16 }}>Frontend Next.js + Backend Laravel</p>

			<AdminStatistics />

			<div style={{ padding: 12, background: "#f5f5f5", borderRadius: 8, marginBottom: 20 }}>
				<b>API Health:</b> {health}
			</div>
			<div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
				<Link href="/scan" style={{ padding: "10px 16px", background: "black", color: "white", borderRadius: 8, textDecoration: "none" }}>
					Buka Pemindai QR
				</Link>
				<a href="/api/health" style={{ padding: "10px 16px", border: "1px solid #ddd", borderRadius: 8, textDecoration: "none" }}>
					Lihat /api/health
				</a>
			</div>
		</main>
	);
}
