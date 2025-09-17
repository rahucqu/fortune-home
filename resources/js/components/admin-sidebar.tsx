import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import {
    Briefcase,
    Building2,
    Home,
    LayoutGrid,
    MapPin,
    Plus,
    Shield,
    Star,
    Users,
    Users2
} from 'lucide-react';

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin',
        icon: LayoutGrid,
    },

    // Property Management Section
    {
        title: 'Properties',
        href: '/admin/properties',
        icon: Home,
    },
    {
        title: 'Add Property',
        href: '/admin/properties/create',
        icon: Plus,
    },
    {
        title: 'Property Types',
        href: '/admin/property-types',
        icon: Building2,
    },
    {
        title: 'Locations',
        href: '/admin/locations',
        icon: MapPin,
    },
    {
        title: 'Agents',
        href: '/admin/agents',
        icon: Briefcase,
    },
    {
        title: 'Amenities',
        href: '/admin/amenities',
        icon: Star,
    },

    // User & Team Management
    {
        title: 'User Management',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Team Management',
        href: '/admin/teams',
        icon: Users2,
    },

    // Content Management
    // {
    //     title: 'Posts',
    //     href: '/admin/posts',
    //     icon: FileText,
    // },
    // {
    //     title: 'Create Post',
    //     href: '/admin/posts/create',
    //     icon: Plus,
    // },
    // {
    //     title: 'Categories',
    //     href: '/admin/categories',
    //     icon: FolderOpen,
    // },
    // {
    //     title: 'Tags',
    //     href: '/admin/tags',
    //     icon: Hash,
    // },
    // {
    //     title: 'Comments',
    //     href: '/admin/comments',
    //     icon: MessageSquare,
    // },
    // {
    //     title: 'Media Library',
    //     href: '/admin/media',
    //     icon: Image,
    // },
    // {
    //     title: 'Upload Media',
    //     href: '/admin/media/create',
    //     icon: Plus,
    // },

    // SEO Management
    // {
    //     title: 'SEO Settings',
    //     href: '/admin/seo-settings',
    //     icon: Search,
    // },

    // // System Settings
    // {
    //     title: 'General Settings',
    //     href: '/admin/settings',
    //     icon: Settings,
    // },
    // {
    //     title: 'Website Settings',
    //     href: '/admin/settings/website',
    //     icon: Globe,
    // },
];const footerNavItems: NavItem[] = [
    //
];

export function AdminSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/admin" prefetch>
                                <div className="flex items-center gap-2">
                                    <Shield className="h-6 w-6 text-orange-500" />
                                    <div className="grid flex-1 text-left text-sm leading-tight">
                                        <span className="truncate font-semibold">Admin Panel</span>
                                        <span className="truncate text-xs">Management Dashboard</span>
                                    </div>
                                </div>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={adminNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
