import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import Image from "next/image";

export default function AboutUsPage() {
  return (
    <div className="space-y-8">
      <Card>
        <CardHeader>
          <CardTitle className="text-3xl font-bold text-primary">About GSB Mandal Thane</CardTitle>
        </CardHeader>
        <CardContent className="prose max-w-none">
          <Image 
            src="https://placehold.co/800x400.png" 
            alt="GSB Mandal Thane Community" 
            width={800} 
            height={400} 
            className="rounded-md mb-6 w-full h-auto"
            data-ai-hint="community gathering"
          />
          <p>
            Welcome to GSB Mandal Thane! We are a vibrant community organization established with the primary objective of fostering unity, preserving cultural heritage, and promoting social welfare among the Goud Saraswat Brahmin (GSB) community residing in and around Thane.
          </p>
          <h2 className="text-2xl font-semibold text-primary mt-6 mb-3">Our Mission</h2>
          <p>
            Our mission is to provide a platform for GSB members to connect, celebrate our rich traditions, support each other, and contribute positively to society. We aim to:
          </p>
          <ul>
            <li>Organize religious and cultural events to uphold our traditions.</li>
            <li>Promote educational and social activities for all age groups.</li>
            <li>Undertake charitable initiatives and community service projects.</li>
            <li>Create a sense of belonging and camaraderie among members.</li>
          </ul>
          <h2 className="text-2xl font-semibold text-primary mt-6 mb-3">Our History</h2>
          <p>
            GSB Mandal Thane was founded several decades ago by a group of visionary individuals who recognized the need for a collective body to represent and serve the GSB community in the rapidly growing city of Thane. Since its inception, the Mandal has grown strength by strength, thanks to the unwavering support and active participation of its members.
          </p>
          <p>
            Over the years, we have organized numerous successful events, including Ganesh Chaturthi celebrations, Kojagiri Pournima, community get-togethers, health camps, and educational seminars.
          </p>
          <h2 className="text-2xl font-semibold text-primary mt-6 mb-3">Looking Ahead</h2>
          <p>
            As we move forward, GSB Mandal Thane is committed to adapting to the evolving needs of our community while staying true to our core values. We envision a future where our Mandal continues to be a beacon of cultural pride, social responsibility, and community spirit.
          </p>
          <p>
            We invite all GSB community members in Thane to join us, participate in our activities, and contribute their ideas and energy to make our Mandal even more dynamic and impactful.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
