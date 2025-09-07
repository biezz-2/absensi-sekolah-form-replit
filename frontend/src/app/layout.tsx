import type { Metadata } from "next";
import "./globals.css";
import "../css/satoshi.css";
import "../css/style.css";
import "flatpickr/dist/flatpickr.min.css";
import "jsvectormap/dist/jsvectormap.css";
import { Sidebar } from "@/components/Layouts/sidebar";
import { Header } from "@/components/Layouts/header";
import { Providers } from "./providers";
import NextTopLoader from "nextjs-toploader";

export const metadata: Metadata = {
  title: "Sistem Absensi QR Siswa",
  description: "Frontend Next.js",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body>
        <Providers>
          <NextTopLoader color="#5750F1" showSpinner={false} />
          <div className="flex min-h-screen">
            <Sidebar />
            <div className="w-full bg-gray-2 dark:bg-[#020d1a]">
              <Header />
              <main className="isolate mx-auto w-full max-w-screen-2xl overflow-hidden p-4 md:p-6 2xl:p-10">
                {children}
              </main>
            </div>
          </div>
        </Providers>
      </body>
    </html>
  );
}
