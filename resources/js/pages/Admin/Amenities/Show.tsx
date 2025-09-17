import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Hash, Home, Tag } from 'lucide-react';

interface Amenity {
    id: number;
    name: string;
    description: string | null;
    category: string;
    icon: string | null;
    is_active: boolean;
    sort_order: number;
    properties_count: number;
    created_at: string;
    updated_at: string;
}

interface AmenitiesShowProps {
    amenity: Amenity;
}

export default function AmenitiesShow({ amenity }: AmenitiesShowProps) {
    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Amenities', href: '/admin/amenities' },
        { title: amenity.name, href: `/admin/amenities/${amenity.id}` },
    ];

    const getCategoryColor = (category: string) => {
        const colors: Record<string, string> = {
            indoor: 'bg-blue-100 text-blue-800',
            outdoor: 'bg-green-100 text-green-800',
            security: 'bg-red-100 text-red-800',
            recreation: 'bg-purple-100 text-purple-800',
            utilities: 'bg-yellow-100 text-yellow-800',
            transportation: 'bg-indigo-100 text-indigo-800',
            'health & wellness': 'bg-pink-100 text-pink-800',
            community: 'bg-orange-100 text-orange-800',
            kitchen: 'bg-teal-100 text-teal-800',
            bathroom: 'bg-cyan-100 text-cyan-800',
            other: 'bg-gray-100 text-gray-800',
        };
        return colors[category.toLowerCase()] || colors.other;
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Amenity: ${amenity.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{amenity.name}</h1>
                        <p className="text-muted-foreground">
                            Amenity details and usage information
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" asChild>
                            <Link href="/admin/amenities">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Amenities
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={`/admin/amenities/${amenity.id}/edit`}>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Amenity
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Amenity Details</CardTitle>
                                <CardDescription>
                                    Information about this amenity
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Name</label>
                                        <p className="text-sm flex items-center">
                                            <Tag className="h-4 w-4 mr-2" />
                                            {amenity.name}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Category</label>
                                        <p className="text-sm">
                                            <Badge className={getCategoryColor(amenity.category)}>
                                                {amenity.category}
                                            </Badge>
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Icon</label>
                                        <p className="text-sm">
                                            {amenity.icon || 'No icon specified'}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Sort Order</label>
                                        <p className="text-sm flex items-center">
                                            <Hash className="h-4 w-4 mr-2" />
                                            {amenity.sort_order}
                                        </p>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Status</label>
                                        <Badge variant={amenity.is_active ? 'default' : 'secondary'}>
                                            {amenity.is_active ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Properties Using</label>
                                        <p className="text-sm flex items-center">
                                            <Home className="h-4 w-4 mr-2" />
                                            {amenity.properties_count} properties
                                        </p>
                                    </div>
                                </div>

                                {amenity.description && (
                                    <>
                                        <Separator />
                                        <div>
                                            <label className="text-sm font-medium text-muted-foreground">Description</label>
                                            <p className="text-sm mt-2 leading-relaxed">{amenity.description}</p>
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
                                    How this amenity is being used
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="text-center p-6">
                                    <div className="text-3xl font-bold text-primary">{amenity.properties_count}</div>
                                    <p className="text-sm text-muted-foreground">Properties featuring this amenity</p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Category Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <label className="text-sm font-medium text-muted-foreground">Category</label>
                                    <div>
                                        <Badge className={getCategoryColor(amenity.category)}>
                                            {amenity.category.charAt(0).toUpperCase() + amenity.category.slice(1)}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Timestamps</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Created</label>
                                    <p className="text-sm">{new Date(amenity.created_at).toLocaleDateString()}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Last Updated</label>
                                    <p className="text-sm">{new Date(amenity.updated_at).toLocaleDateString()}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
