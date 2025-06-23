
'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Mail, MessageCircle, Twitter, Facebook, CalendarDays, UserCircle, ThumbsUp, ThumbsDown } from 'lucide-react';

interface BlogPostItemProps {
  title: string;
  date: string;
  author: string;
  contentHtml: string; // HTML string for content
  imageUrl?: string;
  imageHint?: string;
  videoUrl?: string; // For embedded videos
}

export default function BlogPostItem({ title, date, author, contentHtml, imageUrl, imageHint, videoUrl }: BlogPostItemProps) {
  const [upvotes, setUpvotes] = useState(0);
  const [downvotes, setDownvotes] = useState(0);
  const [votedState, setVotedState] = useState<'upvoted' | 'downvoted' | null>(null);
  const [currentUrl, setCurrentUrl] = useState('');

  useEffect(() => {
    // Ensure window is defined (runs only on client)
    if (typeof window !== 'undefined') {
      setCurrentUrl(window.location.href);
    }
  }, []);

  const handleUpvote = () => {
    if (votedState === 'upvoted') {
      setUpvotes(prev => prev - 1);
      setVotedState(null);
    } else if (votedState === 'downvoted') {
      setUpvotes(prev => prev + 1);
      setDownvotes(prev => prev - 1);
      setVotedState('upvoted');
    } else {
      setUpvotes(prev => prev + 1);
      setVotedState('upvoted');
    }
  };

  const handleDownvote = () => {
    if (votedState === 'downvoted') {
      setDownvotes(prev => prev - 1);
      setVotedState(null);
    } else if (votedState === 'upvoted') {
      setDownvotes(prev => prev + 1);
      setUpvotes(prev => prev - 1);
      setVotedState('downvoted');
    } else {
      setDownvotes(prev => prev + 1);
      setVotedState('downvoted');
    }
  };

  const handleCommentClick = () => {
    console.log(`Comment button clicked for post: ${title}`);
    alert("Commenting feature coming soon!");
  };

  const handleShareEmail = () => {
    const subject = encodeURIComponent(`Check out this post: ${title}`);
    const body = encodeURIComponent(`I found this interesting: ${currentUrl}`);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
  };

  const handleShareTwitter = () => {
    const text = encodeURIComponent(`${title} - ${currentUrl}`);
    window.open(`https://twitter.com/intent/tweet?text=${text}`, '_blank');
  };

  const handleShareFacebook = () => {
    const url = encodeURIComponent(currentUrl);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
  };

  return (
    <Card className="mb-8 shadow-lg">
      <CardHeader className="pb-3">
        <CardTitle className="text-2xl lg:text-3xl font-bold text-primary hover:text-accent">
          {/* Link to a dedicated post page would go here, for now '#' */}
          <Link href="#">{title}</Link>
        </CardTitle>
        <CardDescription className="text-xs text-muted-foreground flex items-center space-x-4 pt-2">
          <span className="flex items-center"><CalendarDays className="h-4 w-4 mr-1" /> {date}</span>
          <span className="flex items-center"><UserCircle className="h-4 w-4 mr-1" /> By {author}</span>
        </CardDescription>
      </CardHeader>
      <CardContent>
        {imageUrl && (
          <div className="mb-4 relative w-full" style={{paddingBottom: '56.25%' /* 16:9 Aspect Ratio */}}>
            <Image 
              src={imageUrl} 
              alt={title} 
              layout="fill"
              objectFit="cover"
              className="rounded-md"
              data-ai-hint={imageHint || "blog post image"}
            />
          </div>
        )}
        {videoUrl && (
          <div className="mb-4 aspect-video">
            <iframe 
              width="100%" 
              height="100%" 
              src={videoUrl}
              title={title}
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
              allowFullScreen
              className="rounded-md"
            ></iframe>
          </div>
        )}
        <div
          className="prose prose-sm sm:prose lg:prose-lg xl:prose-xl max-w-none text-foreground"
          dangerouslySetInnerHTML={{ __html: contentHtml }}
        />
      </CardContent>
      <CardFooter className="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground border-t pt-4">
        <div className="flex items-center space-x-4"> {/* Comments and Votes */}
          <Button variant="ghost" size="sm" className="text-xs p-1 h-auto" onClick={handleCommentClick} aria-label="Comment on post">
            <MessageCircle className="h-4 w-4 mr-1" /> 0 Comments
          </Button>
          <div className="flex items-center space-x-1">
            <Button 
              variant="ghost" 
              size="sm" 
              className={`text-xs p-1 h-auto ${votedState === 'upvoted' ? 'text-accent' : ''}`} 
              onClick={handleUpvote} 
              aria-label="Upvote post"
            >
              <ThumbsUp className={`h-3 w-3 mr-1 ${votedState === 'upvoted' ? 'fill-current' : ''}`} /> {upvotes}
            </Button>
          </div>
          <div className="flex items-center space-x-1">
            <Button 
              variant="ghost" 
              size="sm" 
              className={`text-xs p-1 h-auto ${votedState === 'downvoted' ? 'text-destructive' : ''}`} 
              onClick={handleDownvote} 
              aria-label="Downvote post"
            >
              <ThumbsDown className={`h-3 w-3 mr-1 ${votedState === 'downvoted' ? 'fill-current' : ''}`} /> {downvotes}
            </Button>
          </div>
        </div>
        <div className="flex items-center space-x-2"> {/* Share Buttons */}
          <Button variant="ghost" size="sm" className="text-xs p-1 h-auto" onClick={handleShareEmail} aria-label="Share post via email">
            <Mail className="h-3 w-3 mr-1" /> Email This
          </Button>
          <Button variant="ghost" size="sm" className="text-xs p-1 h-auto" onClick={handleShareTwitter} aria-label="Share post to Twitter">
            <Twitter className="h-3 w-3 mr-1" /> Share to Twitter
          </Button>
          <Button variant="ghost" size="sm" className="text-xs p-1 h-auto" onClick={handleShareFacebook} aria-label="Share post to Facebook">
            <Facebook className="h-3 w-3 mr-1" /> Share to Facebook
          </Button>
        </div>
      </CardFooter>
    </Card>
  );
}
