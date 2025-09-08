import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
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
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    current_team_id?: number;
    owned_teams?: Team[];
    teams?: Team[];
    current_team?: Team;
    all_teams?: Team[];
    is_admin?: boolean;
    roles?: string[];
    [key: string]: unknown; // This allows for additional properties...
}

export interface Team {
    id: number;
    user_id: number;
    name: string;
    personal_team: boolean;
    created_at: string;
    updated_at: string;
    owner?: User;
    users?: TeamMember[];
}

export interface TeamMember {
    id: number;
    name: string;
    email: string;
    pivot: {
        role: string;
        created_at: string;
        updated_at: string;
    };
}
