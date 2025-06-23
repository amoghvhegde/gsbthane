import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import { cookies } from 'next/headers';

// POST /api/auth/logout - Log out a user
export async function POST(req: NextRequest) {
  try {
    // Sign out from Supabase Auth
    const { error } = await supabase.auth.signOut();

    if (error) {
      console.error('Error signing out:', error);
      return NextResponse.json({ error: 'Logout failed' }, { status: 500 });
    }

    // Clear auth cookie
    const cookieStore = cookies();
    cookieStore.delete('sb-auth-token');

    return NextResponse.json({
      message: 'Logged out successfully'
    });
  } catch (error) {
    console.error('Error during logout:', error);
    return NextResponse.json({ error: 'Logout failed' }, { status: 500 });
  }
}

// DELETE /api/auth/logout - Alternative endpoint for logout
export async function DELETE(req: NextRequest) {
  // Reuse the POST implementation
  return POST(req);
}