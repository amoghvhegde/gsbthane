import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import { Booking, BookingItem } from '@/lib/supabase';
import { v4 as uuidv4 } from 'uuid';

// GET /api/bookings - Get all bookings (admin) or bookings for a specific user
export async function GET(req: NextRequest) {
  try {
    const url = new URL(req.url);
    const userId = url.searchParams.get('userId');
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin && !userId) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    let query = supabase.from('bookings').select(`
      *,
      users:user_id (
        first_name,
        middle_name,
        surname,
        email,
        mobile_no
      ),
      booking_items (
        id,
        seva_id,
        quantity,
        price_at_booking,
        sevas:seva_id (
          name,
          description
        )
      )
    `);

    // Filter by user ID if provided
    if (userId) {
      query = query.eq('user_id', userId);
    }

    const { data, error } = await query.order('booking_date', { ascending: false });

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error fetching bookings:', error);
    return NextResponse.json({ error: 'Failed to fetch bookings' }, { status: 500 });
  }
}

// POST /api/bookings - Create a new booking
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { user_id, items, donation_amount = 0, pan_number } = body;

    // Validation
    if (!user_id || !items || !Array.isArray(items) || items.length === 0) {
      return NextResponse.json(
        { error: 'User ID and at least one item are required' },
        { status: 400 }
      );
    }

    // Start a transaction
    const { data: userData, error: userError } = await supabase
      .from('users')
      .select('id')
      .eq('id', user_id)
      .single();

    if (userError || !userData) {
      return NextResponse.json({ error: 'User not found' }, { status: 404 });
    }

    // Fetch seva details and calculate total amount
    let totalAmount = parseFloat(donation_amount) || 0;
    const bookingItems = [];

    for (const item of items) {
      const { seva_id, quantity = 1 } = item;
      
      if (!seva_id) {
        return NextResponse.json(
          { error: 'Seva ID is required for each item' },
          { status: 400 }
        );
      }

      // Get seva price
      const { data: sevaData, error: sevaError } = await supabase
        .from('sevas')
        .select('price, is_active')
        .eq('id', seva_id)
        .single();

      if (sevaError || !sevaData) {
        return NextResponse.json(
          { error: `Seva with ID ${seva_id} not found` },
          { status: 404 }
        );
      }

      if (!sevaData.is_active) {
        return NextResponse.json(
          { error: `Seva with ID ${seva_id} is not active` },
          { status: 400 }
        );
      }

      const itemTotal = sevaData.price * quantity;
      totalAmount += itemTotal;

      bookingItems.push({
        seva_id,
        quantity,
        price_at_booking: sevaData.price
      });
    }

    // Generate a unique receipt ID (format: GSB-YYYY-MMDD-XXXX)
    const today = new Date();
    const dateStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}${String(today.getDate()).padStart(2, '0')}`;
    const randomSuffix = Math.floor(1000 + Math.random() * 9000);
    const receipt_id = `GSB-${dateStr}-${randomSuffix}`;

    // Create booking
    const { data: booking, error: bookingError } = await supabase
      .from('bookings')
      .insert({
        user_id,
        total_amount: totalAmount,
        donation_amount: donation_amount || 0,
        pan_number,
        receipt_id,
        payment_status: 'PENDING' // Will be updated after payment
      })
      .select()
      .single();

    if (bookingError) throw bookingError;

    // Insert booking items
    const bookingItemsWithId = bookingItems.map(item => ({
      ...item,
      booking_id: booking.id
    }));

    const { data: insertedItems, error: itemsError } = await supabase
      .from('booking_items')
      .insert(bookingItemsWithId)
      .select();

    if (itemsError) throw itemsError;

    // Return the created booking with items
    return NextResponse.json(
      {
        ...booking,
        items: insertedItems
      },
      { status: 201 }
    );
  } catch (error) {
    console.error('Error creating booking:', error);
    return NextResponse.json({ error: 'Failed to create booking' }, { status: 500 });
  }
}

// PATCH /api/bookings/:id - Update a booking (mainly for updating payment status)
export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    const body = await req.json();
    
    const { data, error } = await supabase
      .from('bookings')
      .update(body)
      .eq('id', id)
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error updating booking:', error);
    return NextResponse.json({ error: 'Failed to update booking' }, { status: 500 });
  }
}

// DELETE /api/bookings/:id - Delete a booking (admin only)
export async function DELETE(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    
    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    // Delete booking (cascade will delete booking items)
    const { error } = await supabase
      .from('bookings')
      .delete()
      .eq('id', id);

    if (error) throw error;

    return NextResponse.json({ message: 'Booking deleted successfully' });
  } catch (error) {
    console.error('Error deleting booking:', error);
    return NextResponse.json({ error: 'Failed to delete booking' }, { status: 500 });
  }
}