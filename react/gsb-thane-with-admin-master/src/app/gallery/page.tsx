import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import Image from "next/image";

const galleryImages = [
  { src: "https://placehold.co/400x300.png", alt: "Ganesh Chaturthi 2023", caption: "Ganesh Idol Darshan", imageHint: "religious idol" },
  { src: "https://placehold.co/400x300.png", alt: "Cultural Program", caption: "Children's Dance Performance", imageHint: "cultural dance" },
  { src: "https://placehold.co/400x300.png", alt: "Community Gathering", caption: "Annual Sneha Sammelan", imageHint: "community event" },
  { src: "https://placehold.co/400x300.png", alt: "Kojagiri Celebration", caption: "Devotional Singing", imageHint: "festival night" },
  { src: "https://placehold.co/400x300.png", alt: "Volunteer Activity", caption: "Prasad Distribution Team", imageHint: "volunteers group" },
  { src: "https://placehold.co/400x300.png", alt: "Mandal Hall Decoration", caption: "Festive Decorations", imageHint: "event decoration" },
  { src: "https://placehold.co/400x300.png", alt: "Traditional Attire", caption: "Members in Traditional Wear", imageHint: "traditional clothing" },
  { src: "https://placehold.co/400x300.png", alt: "Lighting Ceremony", caption: "Deep Prajwalan", imageHint: "ceremony lights" },
];

export default function GalleryPage() {
  return (
    <div className="space-y-8">
      <Card>
        <CardHeader>
          <CardTitle className="text-3xl font-bold text-primary">Event Gallery</CardTitle>
          <CardDescription>A glimpse into the vibrant events and cherished moments at GSB Mandal Thane.</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {galleryImages.map((image, index) => (
              <div key={index} className="group relative overflow-hidden rounded-lg shadow-md">
                <Image 
                  src={image.src} 
                  alt={image.alt} 
                  width={400} 
                  height={300} 
                  className="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105"
                  data-ai-hint={image.imageHint}
                />
                <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity duration-300 flex items-end p-2">
                  <p className="text-white text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">{image.caption}</p>
                </div>
              </div>
            ))}
          </div>
          <p className="mt-8 text-center text-muted-foreground">
            More photos and videos from our events are regularly updated. Follow us on social media for the latest!
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
