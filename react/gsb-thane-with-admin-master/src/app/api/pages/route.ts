import { NextRequest, NextResponse } from 'next/server';
import { supabase } from '@/lib/supabase';
import { Page } from '@/lib/supabase';

// GET /api/pages - Get all pages or a specific page by slug
export async function GET(req: NextRequest) {
  try {
    const url = new URL(req.url);
    const slug = url.searchParams.get('slug');
    const isAdmin = true; // placeholder - should check JWT token
    
    let query = supabase.from('pages').select(`
      *,
      created_by:created_by (
        first_name,
        middle_name,
        surname
      )
    `);
    
    // Filter by slug if provided
    if (slug) {
      query = query.eq('slug', slug);
      
      // If not admin, only return published pages
      if (!isAdmin) {
        query = query.eq('is_published', true);
      }
      
      const { data, error } = await query.single();
      
      if (error) {
        if (error.code === 'PGRST116') {
          return NextResponse.json({ error: 'Page not found' }, { status: 404 });
        }
        throw error;
      }
      
      return NextResponse.json(data);
    }
    
    // If not admin, only return published pages
    if (!isAdmin) {
      query = query.eq('is_published', true);
    }
    
    const { data, error } = await query.order('created_at', { ascending: false });
    
    if (error) throw error;
    
    return NextResponse.json(data);
  } catch (error) {
    console.error('Error fetching pages:', error);
    return NextResponse.json({ error: 'Failed to fetch pages' }, { status: 500 });
  }
}

// POST /api/pages - Create a new page (admin only)
export async function POST(req: NextRequest) {
  try {
    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token
    const adminId = 'admin-user-id'; // placeholder - should get from session

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const body = await req.json();
    const { title, slug, content, is_published } = body;

    // Validation
    if (!title || !slug || !content) {
      return NextResponse.json(
        { error: 'Title, slug, and content are required' },
        { status: 400 }
      );
    }

    // Check if slug already exists
    const { data: existingPage, error: checkError } = await supabase
      .from('pages')
      .select('id')
      .eq('slug', slug)
      .single();

    if (existingPage) {
      return NextResponse.json(
        { error: 'A page with this slug already exists' },
        { status: 409 }
      );
    }

    // Create page
    const { data, error } = await supabase
      .from('pages')
      .insert({
        title,
        slug,
        content,
        created_by: adminId,
        is_published: is_published !== undefined ? is_published : false
      })
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data, { status: 201 });
  } catch (error) {
    console.error('Error creating page:', error);
    return NextResponse.json({ error: 'Failed to create page' }, { status: 500 });
  }
}

// PATCH /api/pages/:id - Update a page (admin only)
export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    const body = await req.json();

    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }
    
    // Check if the slug is being changed and if the new slug is already in use
    if (body.slug) {
      const { data: pageWithSlug, error: slugError } = await supabase
        .from('pages')
        .select('id')
        .eq('slug', body.slug)
        .neq('id', id)
        .single();
        
      if (pageWithSlug) {
        return NextResponse.json(
          { error: 'A page with this slug already exists' },
          { status: 409 }
        );
      }
    }

    const { data, error } = await supabase
      .from('pages')
      .update(body)
      .eq('id', id)
      .select()
      .single();

    if (error) throw error;

    return NextResponse.json(data);
  } catch (error) {
    console.error('Error updating page:', error);
    return NextResponse.json({ error: 'Failed to update page' }, { status: 500 });
  }
}

// DELETE /api/pages/:id - Delete a page (admin only)
export async function DELETE(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const { id } = params;
    
    // Check for admin authorization
    const isAdmin = true; // placeholder - should check JWT token

    if (!isAdmin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    // Delete page
    const { error } = await supabase
      .from('pages')
      .delete()
      .eq('id', id);

    if (error) throw error;

    return NextResponse.json({ message: 'Page deleted successfully' });
  } catch (error) {
    console.error('Error deleting page:', error);
    return NextResponse.json({ error: 'Failed to delete page' }, { status: 500 });
  }
}