import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, Search, Trash2, Users } from 'lucide-react';
import { useState } from 'react';

interface TeamsIndexProps {
    teams: {
        data: Array<{
            id: number;
            name: string;
            created_at: string;
            users_count: number;
            personal_team: boolean;
            owner: {
                id: number;
                name: string;
                email: string;
            };
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string;
    };
}

export default function TeamsIndex({ teams, filters }: TeamsIndexProps) {
    const [search, setSearch] = useState(filters.search);

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Teams', href: '/admin/teams' },
    ];

    const handleSearch = () => {
        router.get(
            '/admin/teams',
            {
                search,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleDeleteTeam = (teamId: number) => {
        if (confirm('Are you sure you want to delete this team?')) {
            router.delete(`/admin/teams/${teamId}`, {
                onSuccess: () => {
                    // Handle success
                },
            });
        }
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Team Management" />

            <div className="flex-1 space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Team Management</h2>
                        <p className="text-muted-foreground">Manage teams, their owners, and members.</p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Teams</CardTitle>
                        <CardDescription>A list of all teams in the system.</CardDescription>

                        {/* Search */}
                        <div className="flex items-center space-x-2">
                            <div className="relative">
                                <Search className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search teams..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                    className="w-64 pl-8"
                                />
                            </div>
                            <Button onClick={handleSearch} variant="outline">
                                Search
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Team Name</TableHead>
                                    <TableHead>Owner</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Members</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {teams.data.map((team) => (
                                    <TableRow key={team.id}>
                                        <TableCell className="font-medium">{team.name}</TableCell>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{team.owner.name}</div>
                                                <div className="text-sm text-muted-foreground">{team.owner.email}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={team.personal_team ? 'secondary' : 'default'}>
                                                {team.personal_team ? 'Personal' : 'Team'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center space-x-1">
                                                <Users className="h-4 w-4 text-muted-foreground" />
                                                <span>{team.users_count}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>{new Date(team.created_at).toLocaleDateString()}</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex items-center justify-end space-x-2">
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link href={`/admin/teams/${team.id}`}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button variant="ghost" size="sm" asChild>
                                                    <Link href={`/admin/teams/${team.id}/edit`}>
                                                        <Edit className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => handleDeleteTeam(team.id)}
                                                    className="text-destructive hover:text-destructive"
                                                    disabled={team.personal_team}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Pagination info */}
                        <div className="flex items-center justify-between space-x-2 py-4">
                            <div className="text-sm text-muted-foreground">
                                Showing {(teams.current_page - 1) * teams.per_page + 1} to{' '}
                                {Math.min(teams.current_page * teams.per_page, teams.total)} of {teams.total} teams
                            </div>

                            <div className="flex items-center space-x-2">
                                {teams.links?.map((link, index: number) => (
                                    <Button
                                        key={index}
                                        variant={link.active ? 'default' : 'outline'}
                                        size="sm"
                                        disabled={!link.url}
                                        onClick={() => link.url && router.visit(link.url)}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
