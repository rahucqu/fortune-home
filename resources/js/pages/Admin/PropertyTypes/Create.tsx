import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function PropertyTypeCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        icon: '',
        is_active: true,
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Property Types', href: '/admin/property-types' },
        { title: 'Create', href: '/admin/property-types/create' },
    ];

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/admin/property-types');
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Property Type" />

            <div className="space-y-6">
                <div className="flex items-center space-x-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/property-types">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Property Types
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Property Type</h1>
                        <p className="text-muted-foreground">
                            Add a new property type to categorize your listings
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Property Type Information</CardTitle>
                            <CardDescription>
                                Enter the details for the new property type
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="e.g., House, Apartment, Condo"
                                    required
                                />
                                {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Describe this property type..."
                                    rows={3}
                                />
                                {errors.description && <p className="text-sm text-red-600">{errors.description}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="icon">Icon (Emoji or Unicode)</Label>
                                <Input
                                    id="icon"
                                    value={data.icon}
                                    onChange={(e) => setData('icon', e.target.value)}
                                    placeholder="🏠"
                                    maxLength={10}
                                />
                                <p className="text-sm text-muted-foreground">
                                    Enter an emoji or unicode character to represent this property type
                                </p>
                                {errors.icon && <p className="text-sm text-red-600">{errors.icon}</p>}
                            </div>

                            <div className="flex items-center space-x-2">
                                <Switch
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) => setData('is_active', checked)}
                                />
                                <Label htmlFor="is_active">Active</Label>
                                <p className="text-sm text-muted-foreground ml-2">
                                    Inactive property types won't be available for new properties
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex items-center justify-end space-x-4">
                        <Button variant="outline" asChild>
                            <Link href="/admin/property-types">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Property Type'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
