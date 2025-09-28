import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User & {
        permissions: string[];
    };
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    is_active?: boolean;
    active_when?: string; // URL pattern to consider as active (supports wildcards)
    items?: NavItem[]; // Support for submenus
    permission?: string; // Permission(s) required to view this item (supports || and && operators)
}

export interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flashMessages: FlashMessages;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface Permission {
    id: number;
    name: string;
    group: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

export interface Role {
    id: number;
    name: string;
    display_name: string;
    description?: string;
    guard_name: string;
    is_default: boolean;
    created_at: string;
    updated_at: string;
    permissions?: Permission[];
}

export interface Links {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationProps {
    links: Links[];
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: Links[];
}

export interface SimplePaginationProps {
    current_page: number;
    from: number | null;
    to: number | null;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    first_page_url: string | null;
    current_page_url: string;
    path: string;
}

// Enums
export type ListingType = 'sale' | 'rent';
export type PropertyStatus = 'draft' | 'active' | 'inactive' | 'sold' | 'rented' | 'pending';
export type LocationType = 'city' | 'suburb' | 'neighborhood';
export type AmenityCategory = 'interior' | 'exterior' | 'community' | 'security' | 'utilities';
export type FeatureCategory = 'interior' | 'exterior' | 'community';

// Base Model Interface
export interface BaseModel {
    id: number;
    created_at: string;
    updated_at: string;
}

// User Interface
export interface User extends BaseModel {
    name: string;
    email: string;
    profile_photo_url: string;
    email_verified_at: string | null;
    [key: string]: unknown;
}

// Agent Interface
export interface Agent {
    id: number;
    name: string;
    designation: string;
    profile_photo_url: string;
    social_links: { [key: string]: string } | null;
    email: string;
}

// Property Type Interface
export interface PropertyType extends BaseModel {
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    is_active: boolean;
    sort_order: number;
    properties_count?: number;
}

// Location Interface
export interface Location extends BaseModel {
    name: string;
    slug: string;
    type: LocationType;
    state?: string;
    country?: string;
    latitude?: number;
    longitude?: number;
    is_active: boolean;
    sort_order: number;

    // Computed attributes
    full_name: string;
    coordinates?: {
        latitude: number;
        longitude: number;
    };

    // Relationships
    properties_count?: number;
}

// Amenity Interface
export interface Amenity extends BaseModel {
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    category: AmenityCategory;
    is_active: boolean;
    sort_order: number;
}

// Feature Interface
export interface Feature extends BaseModel {
    name: string;
    category: FeatureCategory;
    icon?: string;
    description?: string;
    is_active: boolean;
    sort_order: number;
    pivot?: {
        value: string | null;
    };
}

// Property Image Interface
export interface PropertyImage extends BaseModel {
    property_id: number;
    image_path: string;
    alt_text?: string;
    is_primary: boolean;
    sort_order: number;
    file_size?: number;
    file_type?: string;
    width?: number;
    height?: number;

    // Computed attributes
    image_url: string;
    dimensions: string;
    formatted_file_size?: string;
}

// Property Floor Plan Interface
export interface PropertyFloorPlan extends BaseModel {
    property_id: number;
    name: string;
    image_path?: string;
    bedrooms?: number;
    bathrooms?: number;
    square_feet?: number;
    description?: string;
    sort_order: number;

    // Computed attributes
    image_url?: string;
    formatted_square_feet?: string;
    room_summary?: string;
}

// Property Inquiry Interface
export interface PropertyInquiry extends BaseModel {
    property_id: number;
    user_id?: number;
    name: string;
    email: string;
    phone?: string;
    message: string;
    status: string;
    responded_at?: string;
}

// Property View Interface
export interface PropertyView extends BaseModel {
    property_id: number;
    user_id?: number;
    ip_address?: string;
    user_agent?: string;
}

// Property Favorite Interface
export interface PropertyFavorite extends BaseModel {
    property_id: number;
    user_id: number;
}

// SEO Meta Interface
export interface SeoMeta extends BaseModel {
    model_type: string;
    model_id: number;
    title?: string;
    description?: string;
    keywords?: string;
    og_title?: string;
    og_description?: string;
    og_image?: string;
    twitter_title?: string;
    twitter_description?: string;
    twitter_image?: string;
}

// Blog Post Interface
export interface BlogPost extends BaseModel {
    title: string;
    slug: string;
    excerpt?: string;
    content?: string;
    featured_image_path?: string;
    meta_title?: string;
    meta_description?: string;
    author_id: number;
    category_id: number;
    status: 'draft' | 'published' | 'archived';
    is_featured: boolean;
    views_count: number;
    published_at?: string;

    // Computed attributes
    featured_image_url?: string;
    formatted_published_date?: string;

    // Relationships
    author?: User;
    category?: BlogCategory;
    tags?: BlogTag[];
    comments?: BlogComment[];
    seo_meta?: SeoMeta;
}

// Blog Category Interface
export interface BlogCategory extends BaseModel {
    name: string;
    slug: string;
    description?: string;
    is_active: boolean;
    sort_order: number;
    posts_count?: number;
}

// Blog Tag Interface
export interface BlogTag extends BaseModel {
    name: string;
    slug: string;
    color?: string;
    is_active: boolean;
    posts_count?: number;
}

// Blog Comment Interface
export interface BlogComment extends BaseModel {
    blog_post_id: number;
    user_id?: number;
    parent_id?: number;
    author_name?: string;
    author_email?: string;
    content: string;
    status: 'pending' | 'approved' | 'rejected';
    is_flagged: boolean;

    // Relationships
    user?: User;
    blog_post?: BlogPost;
    parent?: BlogComment;
    replies?: BlogComment[];
}

export interface PropertyTypeForm {
    name: string;
    description?: string;
    icon?: string;
    is_active: boolean;
    sort_order?: number;
}

export interface LocationForm {
    name: string;
    slug?: string;
    type: LocationType;
    state?: string;
    country?: string;
    latitude?: number;
    longitude?: number;
    is_active: boolean;
    sort_order?: number;
}

export interface LocationTypeOption {
    label: string;
    value: LocationType;
    color?: string;
}

export interface LocationFilters {
    search?: string;
    type?: LocationType;
    sort?: string;
    direction?: 'asc' | 'desc';
}

// Main Property Interface
export interface Property extends BaseModel {
    // Basic Information
    title: string;
    slug: string;
    description: string;
    property_type_id: number;
    listing_type: ListingType;
    status: PropertyStatus;

    // Pricing
    price: number;
    price_per_unit?: number;
    currency: string;

    // Property Details
    bedrooms?: number;
    bathrooms?: number;
    half_bathrooms?: number;
    square_feet?: number;
    lot_size?: number;
    year_built?: number;
    garage_spaces?: number;

    // Location
    address: string;
    location_id?: number;
    city: string;
    state: string;
    zip_code: string;
    country: string;
    latitude?: number;
    longitude?: number;
    neighborhood?: string;

    // Listing Details
    mls_number?: string;
    agent_id?: number;
    featured: boolean;
    views_count: number;
    favorites_count: number;
    published_at?: string;
    expires_at?: string;

    // Computed Attributes
    formatted_price?: string;
    full_address?: string;

    // Relationships
    property_type?: PropertyType;
    agent?: User;
    location?: Location;
    amenities?: Amenity[];
    features?: Feature[];
    images?: PropertyImage[];
    primary_image?: PropertyImage;
    floor_plans?: PropertyFloorPlan[];
    inquiries?: PropertyInquiry[];
    views?: PropertyView[];
    favorited_by_users?: User[];
    seo_meta?: SeoMeta;

    // Optional properties for display in components
    image?: string;
    shadow?: boolean;
    has_photos?: number;
    has_videos?: number;
    for_sale?: boolean;
    profile?: string;
    original_price?: number;
    photo_count?: number;
    video_count?: number;
}

// Simplified interface for display components that use mock data
export interface PropertyDisplay {
    id: number;
    title: string;
    slug?: string;
    location?: string | Location | null;
    price: string | number;
    original_price?: string | number;
    bedrooms?: number;
    bathrooms?: number;
    garage_spaces?: number;
    square_feet?: number;
    address?: string;
    city?: string;
    image?: string;
    profile?: string;
    heart_icon?: string;
    featured?: boolean;
    for_sale?: boolean;
    listing_type?: ListingType;
    has_photos?: number;
    has_videos?: number;
    shadow?: boolean;
    photo_count?: number;
    video_count?: number;
    primary_image?: PropertyImage;
    images?: PropertyImage[];
    formatted_price?: string;
}

// Re-export types from property-search module
export { PropertySearchParams, PropertySearchResponse } from './property-search';
