import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface Location {
    id: number;
    name: string;
    slug: string;
    type: string;
    state: string;
    country: string;
    latitude: string | number | null;
    longitude: string | number | null;
    is_active: boolean;
    sort_order: number;
}

interface EditLocationData {
    name: string;
    slug: string;
    type: string;
    state: string;
    country: string;
    latitude: string;
    longitude: string;
    is_active: boolean;
    sort_order: number;
}

interface LocationsEditProps {
    location: Location;
}

export default function LocationsEdit({ location }: LocationsEditProps) {
    const { data, setData, put, processing, errors } = useForm<EditLocationData>({
        name: location.name,
        slug: location.slug,
        type: location.type,
        state: location.state,
        country: location.country,
        latitude: location.latitude?.toString() || '',
        longitude: location.longitude?.toString() || '',
        is_active: location.is_active,
        sort_order: location.sort_order,
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Locations', href: '/admin/locations' },
        { title: location.name, href: `/admin/locations/${location.id}` },
        { title: 'Edit', href: `/admin/locations/${location.id}/edit` },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/locations/${location.id}`);
    };

    const locationTypes = [
        'city',
        'neighborhood',
        'suburb',
        'district',
        'region',
        'area',
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Location: ${location.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Location</h1>
                        <p className="text-muted-foreground">
                            Update the details for {location.name}
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={`/admin/locations/${location.id}`}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Location
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Location Information</CardTitle>
                        <CardDescription>
                            Update the location details below.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Location Name</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g. Downtown"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-destructive">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        value={data.slug}
                                        onChange={(e) => setData('slug', e.target.value)}
                                        placeholder="e.g. downtown"
                                    />
                                    {errors.slug && (
                                        <p className="text-sm text-destructive">{errors.slug}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="type">Type</Label>
                                    <Select
                                        value={data.type}
                                        onValueChange={(value) => setData('type', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {locationTypes.map((type) => (
                                                <SelectItem key={type} value={type}>
                                                    {type.charAt(0).toUpperCase() + type.slice(1)}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.type && (
                                        <p className="text-sm text-destructive">{errors.type}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="sort_order">Sort Order</Label>
                                    <Input
                                        id="sort_order"
                                        type="number"
                                        value={data.sort_order}
                                        onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                        placeholder="0"
                                        min="0"
                                    />
                                    {errors.sort_order && (
                                        <p className="text-sm text-destructive">{errors.sort_order}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="state">State/Province</Label>
                                    <Input
                                        id="state"
                                        value={data.state}
                                        onChange={(e) => setData('state', e.target.value)}
                                        placeholder="e.g. California"
                                    />
                                    {errors.state && (
                                        <p className="text-sm text-destructive">{errors.state}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="country">Country</Label>
                                    <Input
                                        id="country"
                                        value={data.country}
                                        onChange={(e) => setData('country', e.target.value)}
                                        placeholder="e.g. United States"
                                    />
                                    {errors.country && (
                                        <p className="text-sm text-destructive">{errors.country}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="latitude">Latitude</Label>
                                    <Input
                                        id="latitude"
                                        type="number"
                                        step="any"
                                        value={data.latitude}
                                        onChange={(e) => setData('latitude', e.target.value)}
                                        placeholder="e.g. 34.0522"
                                    />
                                    {errors.latitude && (
                                        <p className="text-sm text-destructive">{errors.latitude}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="longitude">Longitude</Label>
                                    <Input
                                        id="longitude"
                                        type="number"
                                        step="any"
                                        value={data.longitude}
                                        onChange={(e) => setData('longitude', e.target.value)}
                                        placeholder="e.g. -118.2437"
                                    />
                                    {errors.longitude && (
                                        <p className="text-sm text-destructive">{errors.longitude}</p>
                                    )}
                                </div>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Switch
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) => setData('is_active', checked)}
                                />
                                <Label htmlFor="is_active">Active</Label>
                            </div>

                            <div className="flex items-center space-x-2">
                                <Button type="submit" disabled={processing}>
                                    <Save className="h-4 w-4 mr-2" />
                                    {processing ? 'Updating...' : 'Update Location'}
                                </Button>
                                <Button type="button" variant="outline" asChild>
                                    <Link href={`/admin/locations/${location.id}`}>Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
