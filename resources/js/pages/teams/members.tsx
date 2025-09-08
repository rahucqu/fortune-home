import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Calendar, Crown, Mail, User } from 'lucide-react';
import { useState } from 'react';

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    pivot: {
        role: 'owner' | 'member';
        joined_at: string;
    };
}

interface Props {
    members: {
        data: User[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    current_team: {
        id: number;
        name: string;
    };
}

export default function Members({ members, current_team }: Props) {
    const [loading, setLoading] = useState(false);

    const handlePageChange = (url: string) => {
        if (!url || loading) return;

        setLoading(true);
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => setLoading(false),
        });
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    return (
        <AppLayout>
            <Head title={`${current_team.name} - Team Members`} />

            <div className="m-10">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Team Members</h1>
                        <p className="text-muted-foreground">Manage members in {current_team.name}</p>
                    </div>

                    <div className="flex items-center space-x-2">
                        <Button variant="outline" asChild>
                            <Link href={route('teams.invitations')}>
                                <Mail className="mr-2 h-4 w-4" />
                                View Invitations
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={route('teams.invite')}>
                                <User className="mr-2 h-4 w-4" />
                                Invite Member
                            </Link>
                        </Button>
                    </div>
                </div>

                <Card className="my-5">
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <User className="h-5 w-5" />
                            <span>Members ({members.meta.total})</span>
                        </CardTitle>
                        <CardDescription>All members of {current_team.name} team.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {members.data.length === 0 ? (
                            <div className="py-12 text-center">
                                <User className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-semibold">No members found</h3>
                                <p className="text-muted-foreground">Start by inviting people to join your team.</p>
                            </div>
                        ) : (
                            <>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Member</TableHead>
                                            <TableHead>Role</TableHead>
                                            <TableHead>Joined</TableHead>
                                            <TableHead className="text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {members.data.map((member) => (
                                            <TableRow key={member.id}>
                                                <TableCell>
                                                    <div className="flex flex-col">
                                                        <span className="font-medium">{member.name}</span>
                                                        <span className="text-sm text-muted-foreground">{member.email}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant={member.pivot.role === 'owner' ? 'default' : 'secondary'}>
                                                        {member.pivot.role === 'owner' && <Crown className="mr-1 h-3 w-3" />}
                                                        {member.pivot.role}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                                        <span className="text-sm">{formatDate(member.pivot.joined_at)}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    {member.pivot.role !== 'owner' && (
                                                        <Button variant="outline" size="sm">
                                                            Remove
                                                        </Button>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>

                                {/* Pagination */}
                                {members.meta.last_page > 1 && (
                                    <div className="flex items-center justify-between pt-4">
                                        <div className="text-sm text-muted-foreground">
                                            Showing {(members.meta.current_page - 1) * members.meta.per_page + 1} to{' '}
                                            {Math.min(members.meta.current_page * members.meta.per_page, members.meta.total)} of {members.meta.total}{' '}
                                            members
                                        </div>
                                        <div className="flex space-x-2">
                                            {members.links.map((link, index) => {
                                                if (link.label === '&laquo; Previous') {
                                                    return (
                                                        <Button
                                                            key={index}
                                                            variant="outline"
                                                            size="sm"
                                                            disabled={!link.url || loading}
                                                            onClick={() => link.url && handlePageChange(link.url)}
                                                        >
                                                            Previous
                                                        </Button>
                                                    );
                                                }
                                                if (link.label === 'Next &raquo;') {
                                                    return (
                                                        <Button
                                                            key={index}
                                                            variant="outline"
                                                            size="sm"
                                                            disabled={!link.url || loading}
                                                            onClick={() => link.url && handlePageChange(link.url)}
                                                        >
                                                            Next
                                                        </Button>
                                                    );
                                                }
                                                if (link.label !== '...' && !isNaN(Number(link.label))) {
                                                    return (
                                                        <Button
                                                            key={index}
                                                            variant={link.active ? 'default' : 'outline'}
                                                            size="sm"
                                                            disabled={!link.url || loading}
                                                            onClick={() => link.url && handlePageChange(link.url)}
                                                        >
                                                            {link.label}
                                                        </Button>
                                                    );
                                                }
                                                return null;
                                            })}
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
