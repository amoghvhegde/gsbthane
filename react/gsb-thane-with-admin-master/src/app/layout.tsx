import type { Metadata } from 'next';
import { Geist } from 'next/font/google'; // Changed to Geist from Geist_Sans
import './globals.css';
import SiteHeader from '@/components/layout/SiteHeader';
import SiteFooter from '@/components/layout/SiteFooter';
import { Toaster } from "@/components/ui/toaster"; // For potential notifications

const geist = Geist({ // Using Geist Sans
  variable: '--font-geist-sans',
  subsets: ['latin'],
});

export const metadata: Metadata = {
  title: 'GSB Mandal Thane - Clone',
  description: 'A clone of the GSB Mandal Thane blogspot site, built with Next.js.',
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={`${geist.variable} antialiased font-sans bg-secondary`}>
        <div className="flex flex-col min-h-screen">
          <SiteHeader />
          <main className="flex-grow container mx-auto px-2 sm:px-4 py-8 bg-background shadow-lg rounded-md my-4">
            {children}
          </main>
          <SiteFooter />
        </div>
        <Toaster />
      </body>
    </html>
  );
}
