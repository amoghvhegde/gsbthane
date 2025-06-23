import Link from 'next/link';
import Image from 'next/image';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { Menu } from 'lucide-react';
import * as SheetPrimitive from '@radix-ui/react-dialog';

const navItems = [
  { label: 'Home', href: '/' },
  { label: 'About Us', href: '/about-us' },
  { label: 'Committee Members', href: '/committee-members' },
  { label: 'Events', href: '/events' },
  { label: 'Seva/Pooja Booking', href: '/seva-pooja-booking' },
  { label: 'Membership', href: '/membership' },
  { label: 'Contact Us', href: '/contact-us' },
  { label: 'Gallery', href: '/gallery' },
];

export default function SiteHeader() {
  return (
    <header className="bg-background shadow-md">
      <div className="container mx-auto px-4 py-6">
        <div className="text-center lg:text-left mb-4">
          <Link href="/" className="inline-flex flex-col sm:flex-row items-center justify-center lg:justify-start space-x-0 sm:space-x-4 group">
            <Image 
              src="/logo.png" 
              alt="GSB Mandal Thane Logo" 
              width={60} 
              height={60} 
              className="rounded-full mb-2 sm:mb-0"
            />
            <div>
              <h1 className="text-4xl lg:text-5xl font-bold text-primary group-hover:text-accent transition-colors">
                GSB Mandal Thane
              </h1>
              <p className="text-muted-foreground mt-1 text-sm lg:text-base">
                || श्री गणेशाय नमः || श्री कुलस्वामिनी प्रसन्न ||
              </p>
            </div>
          </Link>
        </div>
        
        {/* Desktop Navigation */}
        <nav className="hidden lg:block border-t border-border pt-4">
          <ul className="flex flex-wrap justify-center lg:justify-start space-x-4">
            {navItems.map((item) => (
              <li key={item.label}>
                <Link href={item.href} className="text-foreground hover:text-accent transition-colors pb-1">
                  {item.label}
                </Link>
              </li>
            ))}
          </ul>
        </nav>

        {/* Mobile Navigation */}
        <div className="lg:hidden flex justify-end -mt-16 sm:-mt-20"> {/* Adjusted margin for mobile */}
          <Sheet>
            <SheetTrigger asChild>
              <Button variant="outline" size="icon">
                <Menu className="h-6 w-6" />
                <span className="sr-only">Open menu</span>
              </Button>
            </SheetTrigger>
            <SheetContent side="right">
              <nav className="flex flex-col space-y-4 mt-8">
                {navItems.map((item) => {
                  return (
                    <SheetPrimitive.Close key={item.label} asChild>
                      <Link
                        href={item.href}
                        className="text-lg text-foreground hover:text-accent transition-colors"
                      >
                        {item.label}
                      </Link>
                    </SheetPrimitive.Close>
                  );
                })}
              </nav>
            </SheetContent>
          </Sheet>
        </div>
      </div>
    </header>
  );
}
