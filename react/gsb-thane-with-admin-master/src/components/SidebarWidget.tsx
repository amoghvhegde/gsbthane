import type { ReactNode } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface SidebarWidgetProps {
  title: string;
  children: ReactNode;
  className?: string;
}

export default function SidebarWidget({ title, children, className }: SidebarWidgetProps) {
  return (
    <Card className={`mb-6 shadow-lg ${className}`}>
      <CardHeader className="bg-muted/50 p-3 rounded-t-lg">
        <CardTitle className="text-lg font-semibold text-primary">{title}</CardTitle>
      </CardHeader>
      <CardContent className="p-4">
        {children}
      </CardContent>
    </Card>
  );
}
