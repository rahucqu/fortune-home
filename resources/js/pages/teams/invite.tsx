import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Mail, Users } from 'lucide-react';
import { FormEventHandler } from 'react';

interface Props {
    current_team: {
        id: number;
        name: string;
    };
}

export default function Invite({ current_team }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        role: 'member',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('teams.invitations.store'), {
            onSuccess: () => {
                reset();
            },
        });
    };

    return (
        <AppLayout>
            <Head title={`Invite Member - ${current_team.name}`} />

            <div className="m-10">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold tracking-tight">Invite Team Member</h1>

                    <div className="flex items-center space-x-2">
                        <Link href={route('teams.members')}>
                            <Button variant="outline" size="sm">
                                <Users className="mr-2 h-4 w-4" />
                                View Members
                            </Button>
                        </Link>
                        <Link href={route('teams.invitations')}>
                            <Button variant="outline" size="sm">
                                <Mail className="mr-2 h-4 w-4" />
                                View Invitations
                            </Button>
                        </Link>
                    </div>
                </div>

                <Card className="max-w-2xl">
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Mail className="h-5 w-5" />
                            <span>Send Invitation</span>
                        </CardTitle>
                        <CardDescription>
                            Enter the email address of the person you want to invite to your team. They'll receive an email with instructions to join.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="email">Email Address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="member@example.com"
                                    className="w-full"
                                    autoFocus
                                />
                                {errors.email && <p className="text-sm text-red-600 dark:text-red-400">{errors.email}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="role">Role</Label>
                                <Select value={data.role} onValueChange={(value) => setData('role', value)}>
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="Select a role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="member">
                                            <div className="flex items-center space-x-2">
                                                <Users className="h-4 w-4" />
                                                <div>
                                                    <div className="font-medium">Member</div>
                                                    <div className="text-sm text-muted-foreground">Can view and participate in team activities</div>
                                                </div>
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="admin">
                                            <div className="flex items-center space-x-2">
                                                <Users className="h-4 w-4" />
                                                <div>
                                                    <div className="font-medium">Admin</div>
                                                    <div className="text-sm text-muted-foreground">Can manage team members and settings</div>
                                                </div>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.role && <p className="text-sm text-red-600 dark:text-red-400">{errors.role}</p>}
                            </div>

                            <div className="flex items-center justify-end space-x-2 pt-4">
                                <Link href={route('teams.members')}>
                                    <Button variant="outline" type="button">
                                        Cancel
                                    </Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Sending Invitation...' : 'Send Invitation'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
