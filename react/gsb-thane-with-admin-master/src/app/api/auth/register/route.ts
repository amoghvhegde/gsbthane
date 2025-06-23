import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import { supabaseAdmin } from '@/lib/supabase-admin';
import bcrypt from 'bcrypt';
import { USER_TYPES } from '@/lib/db-constants';

// POST /api/auth/register - Register a new user
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { 
      first_name, 
      middle_name, 
      surname, 
      email, 
      mobile_no, 
      password 
    } = body;

    // Validation
    if (!first_name || !surname || !email || !mobile_no || !password) {
      return NextResponse.json(
        { error: 'All fields are required' },
        { status: 400 }
      );
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return NextResponse.json(
        { error: 'Invalid email address' },
        { status: 400 }
      );
    }

    // Check if email already exists
    const { data: existingUser, error: checkError } = await supabase
      .from('users')
      .select('id')
      .eq('email', email)
      .single();

    if (existingUser) {
      return NextResponse.json({ error: 'Email already exists' }, { status: 409 });
    }

    // Hash password
    const password_hash = await bcrypt.hash(password, 10);

    // Create user in Supabase Auth
    const { data: authUser, error: authError } = await supabaseAdmin.auth.admin.createUser({
      email,
      password,
      email_confirm: true, // Auto-confirm email
    });

    if (authError) {
      console.error('Error creating auth user:', authError);
      return NextResponse.json({ error: 'Failed to create user' }, { status: 500 });
    }

    // Create user in database
    const { data: dbUser, error: dbError } = await supabase
      .from('users')
      .insert({
        id: authUser.user.id, // Use the ID from Auth
        first_name,
        middle_name,
        surname,
        email,
        mobile_no,
        password_hash,
        user_type: USER_TYPES.NON_MEMBER, // Default to non-member
        is_admin: false,
      })
      .select()
      .single();

    if (dbError) {
      // Rollback auth user creation if db insert fails
      await supabaseAdmin.auth.admin.deleteUser(authUser.user.id);
      console.error('Error creating user in database:', dbError);
      return NextResponse.json({ error: 'Failed to create user' }, { status: 500 });
    }

    return NextResponse.json(
      { 
        message: 'User registered successfully',
        user: {
          id: dbUser.id,
          first_name: dbUser.first_name,
          surname: dbUser.surname,
          email: dbUser.email,
          user_type: dbUser.user_type
        }
      }, 
      { status: 201 }
    );
  } catch (error) {
    console.error('Error registering user:', error);
    return NextResponse.json({ error: 'Failed to register user' }, { status: 500 });
  }
}