import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function TeamCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Teams', href: '/admin/teams' },
        { title: 'Create', href: '/admin/teams/create' },
    ];

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/admin/teams');
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Team" />

            <div className="space-y-6">
                <div className="flex items-center space-x-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/teams">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Teams
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Team</h1>
                        <p className="text-muted-foreground">
                            Create a new team for organizing users
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Team Information</CardTitle>
                            <CardDescription>
                                Enter the basic details of the team
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
                                />
                                {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                <p className="text-xs text-gray-500">
                                    Choose a descriptive name for the team
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Submit Button */}
                    <div className="flex justify-end space-x-4">
                        <Button type="button" variant="outline" asChild>
                            <Link href="/admin/teams">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Team'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}