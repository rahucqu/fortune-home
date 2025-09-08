import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SharedData, Team } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Check, ChevronDown, Plus, Users } from 'lucide-react';

interface TeamSwitcherProps {
    className?: string;
}

export function TeamSwitcher({ className }: TeamSwitcherProps) {
    const { auth } = usePage<SharedData>().props;

    if (!auth.user || !auth.user.all_teams) {
        return null;
    }

    const currentTeam = auth.user.current_team;
    const allTeams = auth.user.all_teams as Team[];

    const handleSwitchTeam = (teamId: number) => {
        router.post(route('teams.switch', teamId));
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" className={`w-full justify-between ${className}`}>
                    <div className="flex items-center space-x-2">
                        <Users className="h-4 w-4" />
                        <span className="truncate">{currentTeam?.name || 'No Team Selected'}</span>
                    </div>
                    <ChevronDown className="h-4 w-4 opacity-50" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align="start">
                <DropdownMenuLabel>Switch Team</DropdownMenuLabel>
                <DropdownMenuSeparator />

                {allTeams.map((team) => (
                    <DropdownMenuItem
                        key={team.id}
                        onClick={() => handleSwitchTeam(team.id)}
                        className="flex cursor-pointer items-center justify-between"
                    >
                        <div className="flex items-center space-x-2">
                            <Users className="h-4 w-4" />
                            <span>{team.name}</span>
                            {team.personal_team && <span className="text-xs text-muted-foreground">(Personal)</span>}
                        </div>
                        {currentTeam?.id === team.id && <Check className="h-4 w-4" />}
                    </DropdownMenuItem>
                ))}

                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href={route('teams.create')} className="flex cursor-pointer items-center space-x-2">
                        <Plus className="h-4 w-4" />
                        <span>Create Team</span>
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
