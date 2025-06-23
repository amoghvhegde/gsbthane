'use client';

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Phone, Mail, MapPin } from "lucide-react";

export default function ContactUsPage() {
  // Placeholder for form submission handler
  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    // Add form submission logic here
    alert("Form submitted (placeholder - no actual submission).");
  };

  return (
    <div className="space-y-8">
      <Card>
        <CardHeader>
          <CardTitle className="text-3xl font-bold text-primary">Get In Touch</CardTitle>
          <CardDescription>We'd love to hear from you! Whether you have a question, suggestion, or just want to say hello, please reach out.</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {/* Contact Information Section */}
            <div className="space-y-6">
              <div>
                <h3 className="text-xl font-semibold text-primary mb-2">Contact Information</h3>
                <div className="flex items-center space-x-3 mb-2">
                  <MapPin className="h-5 w-5 text-accent" />
                  <p>GSB Mandal Hall, [Full Address], Thane (West), Maharashtra, India - [Pincode]</p>
                </div>
                <div className="flex items-center space-x-3 mb-2">
                  <Mail className="h-5 w-5 text-accent" />
                  <a href="mailto:info@gsbmandalthane.org" className="hover:underline">info@gsbmandalthane.org</a>
                </div>
                <div className="flex items-center space-x-3">
                  <Phone className="h-5 w-5 text-accent" />
                  <p>+91 [Your Phone Number]</p>
                </div>
              </div>
              <div>
                <h3 className="text-xl font-semibold text-primary mb-2">Office Hours</h3>
                <p>Monday - Friday: 10:00 AM - 06:00 PM</p>
                <p>Saturday: 10:00 AM - 02:00 PM</p>
                <p>Sunday: Closed (Open during events)</p>
              </div>
              {/* Placeholder for a map */}
              <div className="aspect-video bg-muted rounded-md flex items-center justify-center">
                <p className="text-muted-foreground">Embedded Map Placeholder</p>
              </div>
            </div>

            {/* Contact Form Section */}
            <form onSubmit={handleSubmit} className="space-y-6">
              <div>
                <Label htmlFor="name" className="text-sm font-medium">Full Name</Label>
                <Input id="name" type="text" placeholder="Your Name" required className="mt-1" />
              </div>
              <div>
                <Label htmlFor="email" className="text-sm font-medium">Email Address</Label>
                <Input id="email" type="email" placeholder="you@example.com" required className="mt-1" />
              </div>
              <div>
                <Label htmlFor="subject" className="text-sm font-medium">Subject</Label>
                <Input id="subject" type="text" placeholder="Regarding..." required className="mt-1" />
              </div>
              <div>
                <Label htmlFor="message" className="text-sm font-medium">Message</Label>
                <Textarea id="message" placeholder="Your message here..." rows={5} required className="mt-1" />
              </div>
              <Button type="submit" className="w-full" size="lg">Send Message</Button>
            </form>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
