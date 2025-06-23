export default function SiteFooter() {
  return (
    <footer className="bg-background border-t border-border mt-12">
      <div className="container mx-auto px-4 py-6 text-center text-muted-foreground text-sm">
        <p>
          &copy; {new Date().getFullYear()} GSB Mandal Thane. All Rights Reserved.
        </p>
      </div>
    </footer>
  );
}
