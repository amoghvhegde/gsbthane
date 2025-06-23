import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import { Membership } from '@/lib/supabase';

// GET /api/memberships - Get all memberships (admin only)
export async function GET(req: NextRequest) {
  try {
    // Check for admin authorization - this should use a proper auth middleware
    const isAdmin = true; // placeholder - should check JWT token
    const url = new URL(req.url);
    const userId = url.searchParams.get('userId');

    if (!isAdmin && !userId) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    let query = supabase.from('memberships').select(`
      *,
      users:user_id (
        first_name,
        middle_name,
        surname,
        email,
        mobile_no
      )
    `);

    // Filter by user ID if provided
    if (userId) {
      query = query.eq('user_id', userId);
    }

    const { data, error } = await query.order('application_date', { ascending: false });

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error fetching memberships:', error);
    return NextResponse.json({ error: 'Failed to fetch memberships' }, { status: 500 });
  }
}

// POST /api/memberships - Create a new membership application
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { 
      user_id, gender, postal_address, pin_code, date_of_birth, 
      occupation, qualification, marital_status, number_of_kids, 
      gotra, kuladevata, math, native_place, other_gsb_memberships, 
      introducer_name, membership_type 
    } = body;

    // Validation
    if (!user_id || !gender || !postal_address || !pin_code || !date_of_birth || 
        !occupation || !qualification || !marital_status || 
        !gotra || !kuladevata || !math || !native_place) {
      return NextResponse.json(
        { error: 'Missing required fields' },
        { status: 400 }
      );
    }

    // Check if user exists
    const { data: existingUser, error: userCheckError } = await supabase
      .from('users')
      .select('id, user_type')
      .eq('id', user_id)
      .single();

    if (userCheckError) {
      return NextResponse.json({ error: 'User not found' }, { status: 404 });
    }

    // Check if membership already exists for this user
    const { data: existingMembership, error: membershipCheckError } = await supabase
      .from('memberships')
      .select('id')
      .eq('user_id', user_id)
      .single();

    if (existingMembership) {
      return NextResponse.json(
        { error: 'Membership application already exists for this user' },
        { status: 409 }
      );
    }

    // Update user type to 'M' (Member)
    await supabase
      .from('users')
      .update({ user_type: 'M' })
      .eq('id', user_id);

    // Create membership application
    const { data, error } = await supabase
      .from('memberships')
      .insert({
        user_id,
        gender,
        postal_address,
        pin_code,
        date_of_birth,
        occupation,
        qualification,
        marital_status,
        number_of_kids,
        gotra,
        kuladevata,
        math,
        native_place,
        other_gsb_memberships,
        introducer_name,
        membership_type: membership_type || 'PATRON',
        status: 'PENDING',
        application_date: new Date().toISOString()
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data, { status: 201 });
  } catch (error) {
    console.error('Error creating membership:', error);
    return NextResponse.json({ error: 'Failed to create membership application' }, { status: 500 });
  }
}

// PATCH /api/memberships/:id - Update membership details
export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    const body = await req.json();
    
    // If updating status to APPROVED, set approval date
    if (body.status === 'APPROVED' && !body.approval_date) {
      body.approval_date = new Date().toISOString();
    }

    const { data, error } = await supabase
      .from('memberships')
      .update(body)
      .eq('id', id)
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error updating membership:', error);
    return NextResponse.json({ error: 'Failed to update membership' }, { status: 500 });
  }
}

// DELETE /api/memberships/:id - Delete a membership (admin only)
export async function DELETE(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    
    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    // Get the user_id before deleting
    const { data: membership } = await supabase
      .from('memberships')
      .select('user_id')
      .eq('id', id)
      .single();

    // Delete the membership
    const { error } = await supabase
      .from('memberships')
      .delete()
      .eq('id', id);

    if (error) throw error;

    // Update the user type back to 'NM'
    if (membership) {
      await supabase
        .from('users')
        .update({ user_type: 'NM' })
        .eq('id', membership.user_id);
    }

    return NextResponse.json({ message: 'Membership deleted successfully' });
  } catch (error) {
    console.error('Error deleting membership:', error);
    return NextResponse.json({ error: 'Failed to delete membership' }, { status: 500 });
  }
}