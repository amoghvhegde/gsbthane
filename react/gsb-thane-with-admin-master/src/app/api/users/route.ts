import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import { User } from '@/lib/supabase';
import bcrypt from 'bcrypt';

// GET /api/users - Get all users (admin only)
export async function GET(req: NextRequest) {
  try {
    // Check for admin authorization - this should use a proper auth middleware
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const { data, error } = await supabase
      .from('users')
      .select('*')
      .order('created_at', { ascending: false });

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error fetching users:', error);
    return NextResponse.json({ error: 'Failed to fetch users' }, { status: 500 });
  }
}

// POST /api/users - Create a new user
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { first_name, middle_name, surname, email, mobile_no, user_type, password } = body;

    // Validation
    if (!first_name || !surname || !email || !mobile_no) {
      return NextResponse.json(
        { error: 'First name, surname, email, and mobile number are required' },
        { status: 400 }
      );
    }

    // Check if email already exists
    const { data: existingUser, error: checkError } = await supabase
      .from('users')
      .select('id')
      .eq('email', email)
      .single();

    if (checkError && checkError.code !== 'PGRST116') {
      throw checkError;
    }

    if (existingUser) {
      return NextResponse.json({ error: 'Email already exists' }, { status: 409 });
    }

    // Hash password if provided (e.g., for admin users)
    let password_hash = null;
    if (password) {
      password_hash = await bcrypt.hash(password, 10);
    }

    // Insert new user
    const { data, error } = await supabase
      .from('users')
      .insert({
        first_name,
        middle_name,
        surname,
        email,
        mobile_no,
        user_type: user_type || 'NM',
        password_hash,
        is_admin: false, // Default to non-admin
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data, { status: 201 });
  } catch (error) {
    console.error('Error creating user:', error);
    return NextResponse.json({ error: 'Failed to create user' }, { status: 500 });
  }
}

// PATCH /api/users/:id - Update a user
export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    const body = await req.json();
    
    // Exclude sensitive fields that shouldn't be updated directly
    const { password_hash, is_admin, ...updateData } = body;

    // Update user
    const { data, error } = await supabase
      .from('users')
      .update(updateData)
      .eq('id', id)
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error updating user:', error);
    return NextResponse.json({ error: 'Failed to update user' }, { status: 500 });
  }
}

// DELETE /api/users/:id - Delete a user (admin only)
export async function DELETE(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    
    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    // Delete user
    const { error } = await supabase
      .from('users')
      .delete()
      .eq('id', id);

    if (error) throw error;

    return NextResponse.json({ message: 'User deleted successfully' });
  } catch (error) {
    console.error('Error deleting user:', error);
    return NextResponse.json({ error: 'Failed to delete user' }, { status: 500 });
  }
}