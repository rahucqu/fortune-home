import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { FormEventHandler } from 'react';

interface LocationCreateProps {
    types: string[];
}

export default function LocationCreate({ types }: LocationCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        type: '',
        state: '',
        country: 'United States',
        latitude: '',
        longitude: '',
        is_active: true,
        sort_order: '',
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Locations', href: '/admin/locations' },
        { title: 'Create', href: '/admin/locations/create' },
    ];

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/admin/locations');
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Location" />

            <div className="space-y-6">
                <div className="flex items-center space-x-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/locations">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Locations
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Location</h1>
                        <p className="text-muted-foreground">
                            Add a new location to the system
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Location Information</CardTitle>
                            <CardDescription>
                                Enter the basic details of the location
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Location Name *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="New York City"
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="type">Type *</Label>
                                    <Select
                                        value={data.type}
                                        onValueChange={(value) => setData('type', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select location type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {types.map((type) => (
                                                <SelectItem key={type} value={type}>
                                                    {type.charAt(0).toUpperCase() + type.slice(1)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.type && <p className="text-sm text-red-600">{errors.type}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="state">State/Province *</Label>
                                    <Input
                                        id="state"
                                        value={data.state}
                                        onChange={(e) => setData('state', e.target.value)}
                                        placeholder="New York"
                                        required
                                    />
                                    {errors.state && <p className="text-sm text-red-600">{errors.state}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="country">Country *</Label>
                                    <Input
                                        id="country"
                                        value={data.country}
                                        onChange={(e) => setData('country', e.target.value)}
                                        required
                                    />
                                    {errors.country && <p className="text-sm text-red-600">{errors.country}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Coordinates */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Geographic Coordinates</CardTitle>
                            <CardDescription>
                                Optional coordinates for map positioning
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="latitude">Latitude</Label>
                                    <Input
                                        id="latitude"
                                        type="number"
                                        step="any"
                                        value={data.latitude}
                                        onChange={(e) => setData('latitude', e.target.value)}
                                        placeholder="40.7128"
                                    />
                                    {errors.latitude && <p className="text-sm text-red-600">{errors.latitude}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="longitude">Longitude</Label>
                                    <Input
                                        id="longitude"
                                        type="number"
                                        step="any"
                                        value={data.longitude}
                                        onChange={(e) => setData('longitude', e.target.value)}
                                        placeholder="-74.0060"
                                    />
                                    {errors.longitude && <p className="text-sm text-red-600">{errors.longitude}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Settings */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Settings</CardTitle>
                            <CardDescription>
                                Configure location settings and display order
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
                                </div>

                                <div className="flex items-center justify-between">
                                    <div className="space-y-0.5">
                                        <Label htmlFor="is_active">Active Status</Label>
                                        <div className="text-sm text-gray-500">
                                            Whether this location should be visible
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
                            <Link href="/admin/locations">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Location'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
