
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Mail, Phone } from "lucide-react";

const committeeMembers = [
  { name: "Kamath Yeshwant Ganesh", role: "President", initials: "KG", email: "sygkamath@gmail.com", phone: "9920451491", imageHint: "leader portrait" },
  { name: "Pai Ashok Ramdas", role: "Vice President", initials: "PR", email: "ashok.r.pai@gmail.com", phone: "9821420440", imageHint: "professional headshot" },
  { name: "Pai Shailesh Devadas", role: "Secretary", initials: "PD", email: "gbisw@yahoo.com", phone: "9820422672", imageHint: "dedicated secretary" },
  { name: "Bhagwat Damodhar Ganpat", role: "Secretary", initials: "BG", email: "damodharbhagwat@gmail.com", phone: "9820135341", imageHint: "organized professional" },
  { name: "Kini Radhakrishna V.", role: "Treasurer", initials: "KV", email: "rvkini@gmail.com", phone: "9869489934", imageHint: "financial expert" },
  { name: "Bhandarkar Dinkar Mukund", role: "Jt. Treasurer", initials: "BM", email: "dmbhandarkar65@gmail.com", phone: "9820831360", imageHint: "assistant treasurer" },
  { name: "Bhandarkar Upendra S.", role: "Member", initials: "BS", email: "dekora2000@gmail.com", phone: "9833483344", imageHint: "community member" },
  { name: "Bhat Ravindra Malpe", role: "Member", initials: "BM", email: "mrbhat1948@rediffmail.com", phone: "9702397626", imageHint: "active volunteer" },
  { name: "Kamath Bhalchandra D", role: "Member", initials: "KD", email: "balukamath26@yahoo.com", phone: "9930591783", imageHint: "committee person" },
  { name: "Kamath Ranganath Vishwanath", role: "Member", initials: "KV", email: "ranka63@gmail.com", phone: "9819231287", imageHint: "dedicated member" },
  { name: "Kamath Sanjay", role: "Member", initials: "KS", email: "askamath@gmail.com", phone: "9892702162", imageHint: "team member" },
  { name: "Pai Ganesh Ramkrishna", role: "Member", initials: "PR", email: "ganpai2003@yahoo.com", phone: "8108523221", imageHint: "community supporter" },
  { name: "Pai Haridas Vishwanath", role: "Member", initials: "PV", email: "haridas.pai@rediffmail.com", phone: "9987506227", imageHint: "board member" },
  { name: "Pai Namrata R", role: "Member", initials: "PR", email: "namrampai@gmail.com", phone: "9820808611", imageHint: "supporting member" },
  { name: "Pai Seema Ashok", role: "Member", initials: "PA", email: "anusha1pai@rediffmail.com", phone: "9821825250", imageHint: "active participant" },
  { name: "Pai Vasudha Vinod", role: "Member", initials: "PV", email: "paivinod@hotmail.com", phone: "9320415018", imageHint: "helpful member" },
  { name: "Pai Venkatesh Ramakrishna", role: "Member", initials: "PR", email: "vpai92@gmail.com", phone: "8652456006", imageHint: "committee volunteer" },
  { name: "Shanbhag Murlidhar S.", role: "Member", initials: "SS", email: "shanmurli@yahoo.co.in", phone: "9987504707", imageHint: "group member" },
  { name: "Shanbhag Tanu Gajanan", role: "Member", initials: "SG", email: "tanu.shanbhag17@gmail.com", phone: "9821318337", imageHint: "mandal member" },
  { name: "Shenoy Prashant Devappa", role: "Member", initials: "SD", email: "prashantshenoy@yahoo.co.in", phone: "9833889255", imageHint: "organization member" },
  { name: "Shenoy Radhakrishna S", role: "Member", initials: "SS", email: "radhakrishnashenoy@seahorsegroup.co.in", phone: "9820254438", imageHint: "valuable member" },
];

export default function CommitteeMembersPage() {
  return (
    <div className="space-y-8">
      <Card>
        <CardHeader>
          <CardTitle className="text-3xl font-bold text-primary">Managing Committee of "The Gowd Saraswat Brahmin Mandal Thane" (WEF 13-08-2023)</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="mb-6 text-muted-foreground">
            Meet the dedicated managing committee (effective from 13-08-2023). Our committee members are volunteers who dedicate their time and effort to organize events, manage operations, and ensure the smooth functioning of the Mandal.
          </p>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {committeeMembers.map((member) => (
              <Card key={member.name} className="shadow-md hover:shadow-lg transition-shadow flex flex-col">
                <CardContent className="p-6 flex flex-col items-center text-center flex-grow">
                  <Avatar className="w-24 h-24 mb-4 border-2 border-primary">
                    <AvatarImage src={`https://placehold.co/100x100.png`} alt={member.name} data-ai-hint={member.imageHint} />
                    <AvatarFallback>{member.initials}</AvatarFallback>
                  </Avatar>
                  <h3 className="text-xl font-semibold text-primary">{member.name}</h3>
                  <p className="text-accent font-medium">{member.role}</p>
                  {member.email && (
                    <div className="flex items-center text-sm text-muted-foreground mt-2">
                      <Mail className="h-4 w-4 mr-2 text-accent" />
                      <a href={`mailto:${member.email}`} className="hover:underline break-all">{member.email}</a>
                    </div>
                  )}
                  {member.phone && (
                    <div className="flex items-center text-sm text-muted-foreground mt-1">
                      <Phone className="h-4 w-4 mr-2 text-accent" />
                      <a href={`tel:${member.phone}`} className="hover:underline">{member.phone}</a>
                    </div>
                  )}
                </CardContent>
              </Card>
            ))}
          </div>
          <p className="mt-8 text-muted-foreground">
            Our committee is constituted as per the Mandal's governing rules. We encourage active participation from all members in the Mandal's activities and governance. If you are interested in contributing or have any suggestions, please feel free to reach out to any of the committee members or contact us through the official channels.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
