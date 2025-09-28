import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { index as favoritesIndex } from '@/routes/admin/favorites';
import { edit } from '@/routes/admin/settings/profile';
import { type User } from '@/types';
import { Can } from '@devwizard/laravel-react-permissions';
import { Link, router } from '@inertiajs/react';
import { Heart, LogOut, Settings } from 'lucide-react';

interface UserMenuContentProps {
    user: User;
}

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <Can permission="favorites.view-own">
                    <DropdownMenuItem asChild>
                        <Link className="block w-full cursor-pointer" href={favoritesIndex()} as="button" prefetch onClick={cleanup}>
                            <Heart className="mr-2" />
                            My Favorites
                        </Link>
                    </DropdownMenuItem>
                </Can>
                <Can permission="settings.view">
                    <DropdownMenuItem asChild>
                        <Link className="block w-full cursor-pointer" href={edit()} as="button" prefetch onClick={cleanup}>
                            <Settings className="mr-2" />
                            Settings
                        </Link>
                    </DropdownMenuItem>
                </Can>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link className="block w-full cursor-pointer" href={logout()} as="button" onClick={handleLogout}>
                    <LogOut className="mr-2" />
                    Log out
                </Link>
            </DropdownMenuItem>
        </>
    );
}
