import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import { Seva } from '@/lib/supabase';

// GET /api/sevas - Get all sevas
export async function GET(req: NextRequest) {
  try {
    const url = new URL(req.url);
    const activeOnly = url.searchParams.get('activeOnly') === 'true';
    
    let query = supabase.from('sevas').select('*');
    
    // Filter by active status if requested
    if (activeOnly) {
      query = query.eq('is_active', true);
    }
    
    const { data, error } = await query.order('price', { ascending: true });

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error fetching sevas:', error);
    return NextResponse.json({ error: 'Failed to fetch sevas' }, { status: 500 });
  }
}

// POST /api/sevas - Create a new seva (admin only)
export async function POST(req: NextRequest) {
  try {
    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const body = await req.json();
    const { name, description, price, is_active } = body;

    // Validation
    if (!name || price === undefined) {
      return NextResponse.json(
        { error: 'Name and price are required' },
        { status: 400 }
      );
    }

    // Check if seva with same name already exists
    const { data: existingSeva, error: checkError } = await supabase
      .from('sevas')
      .select('id')
      .ilike('name', name)
      .single();

    if (existingSeva) {
      return NextResponse.json(
        { error: 'Seva with this name already exists' },
        { status: 409 }
      );
    }

    // Insert new seva
    const { data, error } = await supabase
      .from('sevas')
      .insert({
        name,
        description,
        price,
        is_active: is_active !== undefined ? is_active : true
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data, { status: 201 });
  } catch (error) {
    console.error('Error creating seva:', error);
    return NextResponse.json({ error: 'Failed to create seva' }, { status: 500 });
  }
}

// PATCH /api/sevas/:id - Update a seva (admin only)
export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    const body = await req.json();

    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }
    
    const { data, error } = await supabase
      .from('sevas')
      .update(body)
      .eq('id', id)
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error updating seva:', error);
    return NextResponse.json({ error: 'Failed to update seva' }, { status: 500 });
  }
}

// DELETE /api/sevas/:id - Delete a seva (admin only)
export async function DELETE(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    
    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    // Check if seva is used in any booking
    const { data: bookingItems } = await supabase
      .from('booking_items')
      .select('id')
      .eq('seva_id', id)
      .limit(1);

    if (bookingItems && bookingItems.length > 0) {
      return NextResponse.json(
        { error: 'Cannot delete seva as it is used in bookings' },
        { status: 409 }
      );
    }

    // Delete seva
    const { error } = await supabase
      .from('sevas')
      .delete()
      .eq('id', id);

    if (error) throw error;

    return NextResponse.json({ message: 'Seva deleted successfully' });
  } catch (error) {
    console.error('Error deleting seva:', error);
    return NextResponse.json({ error: 'Failed to delete seva' }, { status: 500 });
  }
}