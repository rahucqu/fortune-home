import { dashboard } from '@/routes/admin';
import { index as amenitiesIndex } from '@/routes/admin/amenities';
import { index as BlogIndex } from '@/routes/admin/blog';

import { index as blogEditRequestsIndex } from '@/routes/admin/blog/edit-requests';
import { index as contactInquiriesIndex } from '@/routes/admin/contact-inquiries';
import { index as locationsIndex } from '@/routes/admin/locations';
import { index as messagesIndex } from '@/routes/admin/messages';
import { index as propertiesIndex } from '@/routes/admin/properties';
import { index as propertyTypesIndex } from '@/routes/admin/property-types';
import { index as rolesIndex } from '@/routes/admin/system/roles';
import { index as usersIndex } from '@/routes/admin/system/users';
import { type NavGroup, type NavItem } from '@/types';
import { Building, FileEdit, Home, LayoutGrid, Mail, MapPin, MessageSquare, Settings, Shield, Star, Users } from 'lucide-react';

// Frontend route imports
import { agents, blog, contact, home, properties } from '@/routes';

// Optimized navigation configuration with better patterns and structure
export const navGroups: NavGroup[] = [
    {
        title: '',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
                permission: '*',
                active_when: dashboard().url,
            },
        ],
    },

    {
        title: 'Properties',
        items: [
            {
                title: 'Properties',
                href: propertiesIndex(),
                icon: Building,
                permission: 'properties.view-all||properties.view-own',
                active_when: propertiesIndex().url + '*',
            },

            {
                title: 'Amenities',
                href: amenitiesIndex(),
                icon: Star,
                permission: 'amenities.view',
                active_when: amenitiesIndex().url + '*',
            },
            {
                title: 'Property Types',
                href: propertyTypesIndex(),
                icon: Home,
                permission: 'property-types.view',
                active_when: propertyTypesIndex().url + '*',
            },
            {
                title: 'Locations',
                href: locationsIndex(),
                icon: MapPin,
                permission: 'locations.view',
                active_when: locationsIndex().url + '*',
            },
        ],
    },

    {
        title: 'Content Management',
        items: [
            {
                title: 'All Posts',
                href: BlogIndex(),
                icon: FileEdit,
                permission: 'blog.view-all',
                active_when: BlogIndex().url,
            },
            {
                title: 'My Posts',
                href: BlogIndex(),
                icon: FileEdit,
                permission: 'blog.view-own',
                active_when: BlogIndex().url,
            },
            {
                title: 'Edit Requests',
                href: blogEditRequestsIndex(),
                icon: FileEdit,
                permission: 'blog-edit-requests.view-all',
                active_when: blogEditRequestsIndex().url + '*',
            },
        ],
    },

    {
        title: 'Communication',
        items: [
            {
                title: 'Contact Inquiries',
                href: contactInquiriesIndex(),
                icon: MessageSquare,
                permission: 'contact-inquiries.view-*',
                active_when: contactInquiriesIndex().url + '*',
            },
            {
                title: 'Messages',
                href: messagesIndex(),
                icon: Mail,
                permission: 'messages.view',
                active_when: messagesIndex().url + '*',
            },
        ],
    },
    {
        title: 'Administration',
        items: [
            {
                title: 'System',
                href: '#',
                icon: Settings,
                items: [
                    {
                        title: 'Roles',
                        href: rolesIndex(),
                        icon: Shield,
                        permission: 'system.roles.view',
                        active_when: rolesIndex().url + '*',
                    },
                    {
                        title: 'Users',
                        href: usersIndex(),
                        icon: Users,
                        permission: 'system.users.view',
                        active_when: usersIndex().url + '*',
                    },
                ],
            },
        ],
    },
];

export const footerNavItems: NavItem[] = [
    //
];

// Frontend navigation configuration
export const frontendNav: NavItem[] = [
    {
        title: 'HOME',
        href: home(),
        active_when: home().url,
    },
    {
        title: 'LISTING',
        href: properties(),
        active_when: properties().url + '*',
    },

    {
        title: 'AGENTS',
        href: agents(),
        active_when: agents().url + '*',
    },
    {
        title: 'Blog',
        href: blog(),
        active_when: blog().url,
    },
    {
        title: 'CONTACT',
        href: contact(),
        active_when: contact().url,
    },
];
