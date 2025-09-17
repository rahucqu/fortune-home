import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Globe, MapPin, Users } from 'lucide-react';

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
    properties_count: number;
    created_at: string;
    updated_at: string;
}

interface LocationsShowProps {
    location: Location;
}

export default function LocationsShow({ location }: LocationsShowProps) {
    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Locations', href: '/admin/locations' },
        { title: location.name, href: `/admin/locations/${location.id}` },
    ];

    const getTypeColor = (type: string) => {
        const colors: Record<string, string> = {
            city: 'bg-blue-100 text-blue-800',
            neighborhood: 'bg-green-100 text-green-800',
            suburb: 'bg-purple-100 text-purple-800',
            district: 'bg-yellow-100 text-yellow-800',
            region: 'bg-indigo-100 text-indigo-800',
            area: 'bg-pink-100 text-pink-800',
        };
        return colors[type.toLowerCase()] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Location: ${location.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{location.name}</h1>
                        <p className="text-muted-foreground">
                            Location details and property information
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" asChild>
                            <Link href="/admin/locations">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Locations
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={`/admin/locations/${location.id}/edit`}>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Location
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Location Details</CardTitle>
                                <CardDescription>
                                    Information about this location
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Name</label>
                                        <p className="text-sm flex items-center">
                                            <MapPin className="h-4 w-4 mr-2" />
                                            {location.name}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Slug</label>
                                        <p className="text-sm text-muted-foreground">{location.slug}</p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Type</label>
                                        <p className="text-sm">
                                            <Badge className={getTypeColor(location.type)}>
                                                {location.type}
                                            </Badge>
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Sort Order</label>
                                        <p className="text-sm">{location.sort_order}</p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">State</label>
                                        <p className="text-sm">{location.state}</p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Country</label>
                                        <p className="text-sm flex items-center">
                                            <Globe className="h-4 w-4 mr-2" />
                                            {location.country}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Status</label>
                                        <Badge variant={location.is_active ? 'default' : 'secondary'}>
                                            {location.is_active ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Properties</label>
                                        <p className="text-sm flex items-center">
                                            <Users className="h-4 w-4 mr-2" />
                                            {location.properties_count} properties
                                        </p>
                                    </div>
                                </div>

                                {location.latitude && location.longitude && (
                                    <>
                                        <Separator />
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">Coordinates</label>
                                            <div className="text-sm mt-2 space-y-1">
                                                <p>Latitude: {parseFloat(location.latitude.toString()).toFixed(6)}</p>
                                                <p>Longitude: {parseFloat(location.longitude.toString()).toFixed(6)}</p>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Usage Statistics</CardTitle>
                                <CardDescription>
                                    How this location is being used
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="text-center p-6">
                                    <div className="text-3xl font-bold text-primary">{location.properties_count}</div>
                                    <p className="text-sm text-muted-foreground">Properties in this location</p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Location Type</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <label className="text-sm font-medium text-muted-foreground">Type</label>
                                    <div>
                                        <Badge className={getTypeColor(location.type)}>
                                            {location.type.charAt(0).toUpperCase() + location.type.slice(1)}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {location.latitude && location.longitude && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Map Preview</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="aspect-square bg-muted rounded-lg flex items-center justify-center">
                                        <div className="text-center text-sm text-muted-foreground">
                                            <MapPin className="h-8 w-8 mx-auto mb-2" />
                                            <p>Map integration</p>
                                            <p className="text-xs">
                                                {parseFloat(location.latitude.toString()).toFixed(4)}, {parseFloat(location.longitude.toString()).toFixed(4)}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle>Timestamps</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Created</label>
                                    <p className="text-sm">{new Date(location.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Last Updated</label>
                                    <p className="text-sm">{new Date(location.updated_at).toLocaleDateString()}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
