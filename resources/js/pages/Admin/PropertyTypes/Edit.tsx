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

interface PropertyTypeEditProps {
    propertyType: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        icon: string | null;
        is_active: boolean;
        sort_order: number | null;
    };
}

export default function PropertyTypeEdit({ propertyType }: PropertyTypeEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: propertyType.name || '',
        description: propertyType.description || '',
        icon: propertyType.icon || '',
        is_active: propertyType.is_active ?? true,
        sort_order: propertyType.sort_order?.toString() || '',
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Property Types', href: '/admin/property-types' },
        { title: 'Edit', href: `/admin/property-types/${propertyType.id}/edit` },
    ];

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/admin/property-types/${propertyType.id}`);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Property Type: ${propertyType.name}`} />

            <div className="space-y-6">
                <div className="flex items-center space-x-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/property-types">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Property Types
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Property Type</h1>
                        <p className="text-muted-foreground">
                            Update the property type information
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Property Type Information</CardTitle>
                            <CardDescription>
                                Update the basic details of the property type
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Name *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Apartment"
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="icon">Icon</Label>
                                    <Input
                                        id="icon"
                                        value={data.icon}
                                        onChange={(e) => setData('icon', e.target.value)}
                                        placeholder="building"
                                    />
                                    {errors.icon && <p className="text-sm text-red-600">{errors.icon}</p>}
                                    <p className="text-xs text-gray-500">
                                        Icon name from your icon library (e.g., building, home, castle)
                                    </p>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Multi-story residential buildings with individual units"
                                    rows={3}
                                />
                                {errors.description && <p className="text-sm text-red-600">{errors.description}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Settings */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Settings</CardTitle>
                            <CardDescription>
                                Configure property type settings and display order
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="sort_order">Sort Order</Label>
                                    <Input
                                        id="sort_order"
                                        type="number"
                                        min="0"
                                        value={data.sort_order}
                                        onChange={(e) => setData('sort_order', e.target.value)}
                                        placeholder="0"
                                    />
                                    {errors.sort_order && <p className="text-sm text-red-600">{errors.sort_order}</p>}
                                    <p className="text-xs text-gray-500">
                                        Lower numbers appear first in listings
                                    </p>
                                </div>

                                <div className="flex items-center justify-between">
                                    <div className="space-y-0.5">
                                        <Label htmlFor="is_active">Active Status</Label>
                                        <div className="text-sm text-gray-500">
                                            Whether this property type should be available for selection
                                        </div>
                                    </div>
                                    <Switch
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked)}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Submit Button */}
                    <div className="flex justify-end space-x-4">
                        <Button type="button" variant="outline" asChild>
                            <Link href="/admin/property-types">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Updating...' : 'Update Property Type'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}