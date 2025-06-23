
import Image from 'next/image';
import Link from 'next/link';
import SidebarWidget from '@/components/SidebarWidget';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, Mail, Rss } from 'lucide-react';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import ClientOnly from '@/components/ClientOnly';

const blogArchive = [
  { year: "2023", months: ["December (1)", "November (2)", "October (3)"] },
  { year: "2022", months: ["July (5)", "June (1)"] },
];

const labels = [
  "Annual Event", "Meeting", "Cultural Program", "Announcement", "Festival"
];

export default function RightSidebar() {
  return (
    <div className="w-full">
      <SidebarWidget title="About GSB Mandal Thane">
        <Image 
          src="https://placehold.co/300x200.png" 
          alt="GSB Mandal Thane" 
          width={300} 
          height={200} 
          className="rounded-md mb-2 w-full h-auto"
          data-ai-hint="community organization" 
        />
        <p className="text-sm text-muted-foreground">
          GSB Mandal Thane is a community organization dedicated to preserving and promoting Goud Saraswat Brahmin culture and traditions in Thane.
        </p>
      </SidebarWidget>

      <SidebarWidget title="Search This Blog">
        <ClientOnly>
          <div className="flex space-x-2">
            <Input type="text" placeholder="Search..." className="flex-grow" />
            <Button variant="outline" size="icon" aria-label="Search blog posts">
              <Search className="h-4 w-4" />
            </Button>
          </div>
        </ClientOnly>
      </SidebarWidget>

      <SidebarWidget title="Blog Archive">
        <Accordion type="single" collapsible className="w-full">
          {blogArchive.map((archiveItem) => (
            <AccordionItem value={archiveItem.year} key={archiveItem.year}>
              <AccordionTrigger className="text-sm py-2">{archiveItem.year}</AccordionTrigger>
              <AccordionContent>
                <ul className="space-y-1 pl-4">
                  {archiveItem.months.map(month => (
                    <li key={month}><Link href="#" className="text-sm hover:underline">{month}</Link></li>
                  ))}
                </ul>
              </AccordionContent>
            </AccordionItem>
          ))}
        </Accordion>
      </SidebarWidget>

      <SidebarWidget title="Labels">
        <ul className="space-y-1">
          {labels.map(label => (
            <li key={label}><Link href="#" className="text-sm hover:underline">{label}</Link></li>
          ))}
        </ul>
      </SidebarWidget>

      <SidebarWidget title="Follow by Email">
        <ClientOnly>
          <div className="flex space-x-2">
            <Input type="email" placeholder="Email address..." className="flex-grow" />
            <Button variant="default">
              <Mail className="h-4 w-4 mr-2" /> Submit
            </Button>
          </div>
        </ClientOnly>
      </SidebarWidget>
      
      <SidebarWidget title="Subscribe To">
        <ul className="space-y-1">
          <li><Link href="#" className="text-sm hover:underline flex items-center"><Rss className="h-4 w-4 mr-2 text-orange-500" /> Posts (Atom)</Link></li>
        </ul>
      </SidebarWidget>

      <SidebarWidget title="Total Pageviews">
        <p className="text-3xl font-bold text-primary">1,234,567</p>
      </SidebarWidget>
    </div>
  );
}

