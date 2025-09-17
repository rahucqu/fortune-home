import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Trash2, UserPlus } from 'lucide-react';
import { FormEventHandler } from 'react';

interface TeamEditProps {
    team: {
        id: number;
        name: string;
        personal_team: boolean;
        owner: {
            id: number;
            name: string;
            email: string;
        };
        users: Array<{
            id: number;
            name: string;
            email: string;
            membership: {
                role: string;
                created_at: string;
            };
        }>;
    };
}

export default function TeamEdit({ team }: TeamEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: team.name || '',
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Teams', href: '/admin/teams' },
        { title: 'Edit', href: `/admin/teams/${team.id}/edit` },
    ];

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/admin/teams/${team.id}`);
    };

    const handleRemoveMember = (userId: number) => {
        if (confirm('Are you sure you want to remove this team member?')) {
            // Handle member removal
            console.log('Remove member:', userId);
        }
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Team: ${team.name}`} />

            <div className="space-y-6">
                <div className="flex items-center space-x-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/teams">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Teams
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Team</h1>
                        <p className="text-muted-foreground">
                            Update team information and manage members
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Team Information</CardTitle>
                            <CardDescription>
                                Update the basic details of the team
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Team Name *</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Development Team"
                                    required
                                    disabled={team.personal_team}
                                />
                                {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                {team.personal_team && (
                                    <p className="text-xs text-gray-500">
                                        Personal teams cannot be renamed
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Team Owner */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Team Owner</CardTitle>
                            <CardDescription>
                                The user who owns and manages this team
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-3">
                                <div className="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span className="text-sm font-medium text-gray-600">
                                        {team.owner.name.charAt(0).toUpperCase()}
                                    </span>
                                </div>
                                <div>
                                    <div className="font-medium">{team.owner.name}</div>
                                    <div className="text-sm text-gray-500">{team.owner.email}</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Team Members */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>Team Members</CardTitle>
                                    <CardDescription>
                                        Manage users who belong to this team
                                    </CardDescription>
                                </div>
                                <Button variant="outline" size="sm">
                                    <UserPlus className="h-4 w-4 mr-2" />
                                    Add Member
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {team.users.length > 0 ? (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>User</TableHead>
                                            <TableHead>Role</TableHead>
                                            <TableHead>Joined</TableHead>
                                            <TableHead className="w-[100px]">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {team.users.map((user) => (
                                            <TableRow key={user.id}>
                                                <TableCell>
                                                    <div className="flex items-center space-x-3">
                                                        <div className="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                            <span className="text-xs font-medium text-gray-600">
                                                                {user.name.charAt(0).toUpperCase()}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div className="font-medium">{user.name}</div>
                                                            <div className="text-sm text-gray-500">{user.email}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {user.membership.role}
                                                    </span>
                                                </TableCell>
                                                <TableCell className="text-sm text-gray-500">
                                                    {new Date(user.membership.created_at).toLocaleDateString()}
                                                </TableCell>
                                                <TableCell>
                                                    {user.id !== team.owner.id && (
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleRemoveMember(user.id)}
                                                            className="text-red-600 hover:text-red-700"
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            ) : (
                                <div className="text-center py-6">
                                    <p className="text-gray-500">No team members found</p>
                                    <Button variant="outline" size="sm" className="mt-2">
                                        <UserPlus className="h-4 w-4 mr-2" />
                                        Add First Member
                                    </Button>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Submit Button */}
                    <div className="flex justify-end space-x-4">
                        <Button type="button" variant="outline" asChild>
                            <Link href="/admin/teams">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing || team.personal_team}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Updating...' : 'Update Team'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}