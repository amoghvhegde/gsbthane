import BlogPostItem from '@/components/BlogPostItem';
import RightSidebar from '@/components/layout/RightSidebar';

// Sample blog post data
const posts = [
  {
    title: "GSB Mandal Thane - Annual General Meeting 2023",
    date: "October 28, 2023",
    author: "GSB Mandal Thane",
    contentHtml: `
      <p>The Annual General Meeting (AGM) of GSB Mandal Thane was held on <strong>Sunday, 22nd October 2023</strong>. We thank all members for their active participation and valuable suggestions.</p>
      <p>Key discussions included a review of the past year's activities, financial reporting, and planning for upcoming events. The committee expressed gratitude for the community's continued support.</p>
      <p>Further details and minutes of the meeting will be shared with members via email shortly.</p>
    `,
    imageUrl: "https://placehold.co/600x400.png",
    imageHint: "meeting community",
  },
  {
    title: "Successful Ganesh Chaturthi Celebrations 2023",
    date: "September 30, 2023",
    author: "GSB Mandal Thane",
    contentHtml: `
      <p>We are delighted to share the success of our Ganesh Chaturthi celebrations for 2023. The event saw enthusiastic participation from the community, with various cultural programs and traditional rituals.</p>
      <p>The Mandal extends its heartfelt thanks to all volunteers, donors, and attendees who made this event a grand success. Your contributions and support are invaluable.</p>
      <p>Here's a glimpse of the festivities:</p>
    `,
    videoUrl: "https://www.youtube.com/embed/dQw4w9WgXcQ", // Placeholder video
  },
  {
    title: "Upcoming Kojagiri Pournima Event",
    date: "September 15, 2023",
    author: "GSB Mandal Thane",
    contentHtml: `
      <p>GSB Mandal Thane invites all members and their families to celebrate Kojagiri Pournima with us. Join us for an evening of devotion, music, and community bonding.</p>
      <p><strong>Date:</strong> To be announced</p>
      <p><strong>Venue:</strong> Mandal Hall, Thane</p>
      <p>More details regarding the program schedule and contributions will be shared soon. We look forward to your presence.</p>
    `,
    imageUrl: "https://placehold.co/600x400.png",
    imageHint: "festival celebration",
  },
];

export default function HomePage() {
  return (
    <div className="flex flex-col lg:flex-row gap-8">
      <div className="flex-grow lg:w-[calc(100%-20rem-2rem)] xl:w-[calc(100%-24rem-2rem)]"> {/* Main content area for blog posts */}
        {posts.map((post, index) => (
          <BlogPostItem
            key={index}
            title={post.title}
            date={post.date}
            author={post.author}
            contentHtml={post.contentHtml}
            imageUrl={post.imageUrl}
            imageHint={post.imageHint}
            videoUrl={post.videoUrl}
          />
        ))}
      </div>
      <aside className="lg:w-80 xl:w-96 flex-shrink-0"> {/* Sidebar area */}
        <RightSidebar />
      </aside>
    </div>
  );
}
