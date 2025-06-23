import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import bcrypt from 'bcrypt';
import { cookies } from 'next/headers';

// POST /api/auth/login - Authenticate a user
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { email, password } = body;

    // Validation
    if (!email || !password) {
      return NextResponse.json(
        { error: 'Email and password are required' },
        { status: 400 }
      );
    }

    // Get user from database to check password
    const { data: dbUser, error: dbError } = await supabase
      .from('users')
      .select('id, password_hash, email, first_name, surname, is_admin, user_type')
      .eq('email', email)
      .single();

    if (dbError || !dbUser) {
      return NextResponse.json({ error: 'Invalid email or password' }, { status: 401 });
    }

    // Verify password
    const isPasswordValid = await bcrypt.compare(password, dbUser.password_hash || '');
    if (!isPasswordValid) {
      return NextResponse.json({ error: 'Invalid email or password' }, { status: 401 });
    }

    // Sign in with Supabase Auth
    const { data: authData, error: authError } = await supabase.auth.signInWithPassword({
      email,
      password,
    });

    if (authError) {
      console.error('Error signing in with Supabase Auth:', authError);
      return NextResponse.json({ error: 'Authentication failed' }, { status: 500 });
    }

    // Set auth cookie
    const cookieStore = cookies();
    cookieStore.set('sb-auth-token', authData.session.access_token, {
      path: '/',
      maxAge: 60 * 60 * 24 * 7, // 1 week
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production',
      httpOnly: true,
    });

    // Return user data (excluding sensitive info)
    return NextResponse.json({
      message: 'Login successful',
      user: {
        id: dbUser.id,
        email: dbUser.email,
        first_name: dbUser.first_name,
        surname: dbUser.surname,
        is_admin: dbUser.is_admin,
        user_type: dbUser.user_type
      },
    });
  } catch (error) {
    console.error('Error logging in:', error);
    return NextResponse.json({ error: 'Login failed' }, { status: 500 });
  }
}

// GET /api/auth/login - Check if user is logged in
export async function GET(req: NextRequest) {
  try {
    // Get auth user
    const { data: { session } } = await supabase.auth.getSession();

    if (!session) {
      return NextResponse.json({ authenticated: false });
    }

    // Get user data from database
    const { data: userData, error } = await supabase
      .from('users')
      .select('id, email, first_name, surname, is_admin, user_type')
      .eq('id', session.user.id)
      .single();

    if (error || !userData) {
      return NextResponse.json({ authenticated: false });
    }

    return NextResponse.json({
      authenticated: true,
      user: userData,
    });
  } catch (error) {
    console.error('Error checking authentication:', error);
    return NextResponse.json({ authenticated: false, error: 'Authentication check failed' });
  }
}