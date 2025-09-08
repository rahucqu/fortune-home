import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Calendar, CheckCircle, Clock, Mail, Users, XCircle } from 'lucide-react';
import { useState } from 'react';

interface TeamInvitation {
    id: number;
    email: string;
    role: 'owner' | 'member';
    status: 'pending' | 'accepted' | 'expired';
    created_at: string;
    expires_at: string;
}

interface Props {
    invitations: {
        data: TeamInvitation[];
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

export default function Invitations({ invitations, current_team }: Props) {
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

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'accepted':
                return <CheckCircle className="h-4 w-4 text-green-600" />;
            case 'expired':
                return <XCircle className="h-4 w-4 text-red-600" />;
            case 'pending':
            default:
                return <Clock className="h-4 w-4 text-yellow-600" />;
        }
    };

    const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
        switch (status) {
            case 'accepted':
                return 'default';
            case 'expired':
                return 'destructive';
            case 'pending':
            default:
                return 'secondary';
        }
    };

    return (
        <AppLayout>
            <Head title={`${current_team.name} - Team Invitations`} />

            <div className="m-10">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Team Invitations</h1>
                        <p className="text-muted-foreground">Manage invitations for {current_team.name}</p>
                    </div>

                    <div className="flex items-center space-x-2">
                        <Button variant="outline" asChild>
                            <Link href={route('teams.members')}>
                                <Users className="mr-2 h-4 w-4" />
                                View Members
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={route('teams.invite')}>
                                <Mail className="mr-2 h-4 w-4" />
                                Send Invitation
                            </Link>
                        </Button>
                    </div>
                </div>

                <Card className="my-5">
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Mail className="h-5 w-5" />
                            <span>Invitations ({invitations.meta.total})</span>
                        </CardTitle>
                        <CardDescription>All pending and sent invitations for {current_team.name} team.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {invitations.data.length === 0 ? (
                            <div className="py-12 text-center">
                                <Mail className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-semibold">No invitations found</h3>
                                <p className="text-muted-foreground">Start by sending invitations to people you want to join your team.</p>
                                <div className="mt-4">
                                    <Button asChild>
                                        <Link href={route('teams.invite')}>Send First Invitation</Link>
                                    </Button>
                                </div>
                            </div>
                        ) : (
                            <>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Email</TableHead>
                                            <TableHead>Role</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Sent</TableHead>
                                            <TableHead>Expires</TableHead>
                                            <TableHead className="text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {invitations.data.map((invitation) => (
                                            <TableRow key={invitation.id}>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        <Mail className="h-4 w-4 text-muted-foreground" />
                                                        <span className="font-medium">{invitation.email}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline">{invitation.role}</Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        {getStatusIcon(invitation.status)}
                                                        <Badge variant={getStatusVariant(invitation.status)}>{invitation.status}</Badge>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                                        <span className="text-sm">{formatDate(invitation.created_at)}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        <Clock className="h-4 w-4 text-muted-foreground" />
                                                        <span className="text-sm">{formatDate(invitation.expires_at)}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        {invitation.status === 'pending' && (
                                                            <>
                                                                <Button variant="outline" size="sm">
                                                                    Resend
                                                                </Button>
                                                                <Button variant="destructive" size="sm">
                                                                    Cancel
                                                                </Button>
                                                            </>
                                                        )}
                                                        {invitation.status === 'expired' && (
                                                            <Button variant="outline" size="sm">
                                                                Resend
                                                            </Button>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>

                                {/* Pagination */}
                                {invitations.meta.last_page > 1 && (
                                    <div className="flex items-center justify-between pt-4">
                                        <div className="text-sm text-muted-foreground">
                                            Showing {(invitations.meta.current_page - 1) * invitations.meta.per_page + 1} to{' '}
                                            {Math.min(invitations.meta.current_page * invitations.meta.per_page, invitations.meta.total)} of{' '}
                                            {invitations.meta.total} invitations
                                        </div>
                                        <div className="flex space-x-2">
                                            {invitations.links.map((link, index) => {
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
