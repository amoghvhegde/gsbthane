import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import Image from "next/image";
import { Button } from "@/components/ui/button";
import Link from "next/link";
import { CalendarDays, MapPin } from "lucide-react";

const events = [
  {
    title: "Ganesh Chaturthi Celebration 2024",
    date: "September 2024 (Exact dates TBD)",
    location: "Mandal Hall, Thane",
    description: "Join us for our grand Ganesh Chaturthi celebrations. Featuring daily poojas, cultural programs, and prasad distribution. A cornerstone event for our community.",
    imageUrl: "https://placehold.co/600x300.png",
    imageHint: "religious festival",
    status: "Upcoming"
  },
  {
    title: "Kojagiri Pournima 2024",
    date: "October 2024 (Exact date TBD)",
    location: "Mandal Hall, Thane",
    description: "Celebrate Kojagiri Pournima with devotional music, traditional delicacies, and community gathering under the autumn full moon.",
    imageUrl: "https://placehold.co/600x300.png",
    imageHint: "cultural event night",
    status: "Upcoming"
  },
  {
    title: "Annual General Meeting 2023",
    date: "October 22, 2023",
    location: "Online & Mandal Hall",
    description: "Recap of the year's activities, financial overview, and planning for the future. Member participation is crucial.",
    imageUrl: "https://placehold.co/600x300.png",
    imageHint: "community meeting",
    status: "Past"
  },
   {
    title: "Summer Health Camp",
    date: "May 15, 2023",
    location: "Community Center, Thane",
    description: "A free health check-up camp organized for members and their families, featuring general physicians and specialists.",
    imageUrl: "https://placehold.co/600x300.png",
    imageHint: "health medical",
    status: "Past"
  }
];

export default function EventsPage() {
  return (
    <div className="space-y-8">
      <Card>
        <CardHeader>
          <CardTitle className="text-3xl font-bold text-primary">Upcoming & Past Events</CardTitle>
          <CardDescription>Stay updated on all activities and celebrations organized by GSB Mandal Thane.</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-6">
            {events.map((event) => (
              <Card key={event.title} className="overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                <div className="md:flex">
                  <div className="md:w-1/3 relative h-48 md:h-auto">
                    <Image 
                      src={event.imageUrl} 
                      alt={event.title} 
                      layout="fill" 
                      objectFit="cover"
                      data-ai-hint={event.imageHint} 
                    />
                  </div>
                  <div className="md:w-2/3">
                    <CardHeader>
                      <CardTitle className="text-xl font-semibold text-primary">{event.title} 
                        <span className={`ml-2 text-xs font-medium px-2 py-0.5 rounded-full ${event.status === 'Upcoming' ? 'bg-accent/30 text-accent-foreground' : 'bg-muted text-muted-foreground'}`}>
                          {event.status}
                        </span>
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="flex items-center text-sm text-muted-foreground mb-1">
                        <CalendarDays className="h-4 w-4 mr-2" /> {event.date}
                      </div>
                      <div className="flex items-center text-sm text-muted-foreground mb-2">
                        <MapPin className="h-4 w-4 mr-2" /> {event.location}
                      </div>
                      <p className="text-sm text-foreground">{event.description}</p>
                    </CardContent>
                    <CardFooter>
                      {event.status === 'Upcoming' && (
                        <Button asChild variant="default">
                          <Link href="#">Learn More & RSVP</Link>
                        </Button>
                      )}
                       {event.status === 'Past' && (
                        <Button asChild variant="outline">
                          <Link href="#">View Gallery</Link>
                        </Button>
                      )}
                    </CardFooter>
                  </div>
                </div>
              </Card>
            ))}
          </div>
          <p className="mt-8 text-center text-muted-foreground">
            For the latest updates on events, please follow our announcements or subscribe to our newsletter.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
