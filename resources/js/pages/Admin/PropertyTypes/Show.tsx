import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, Eye, Plus } from 'lucide-react';

interface PropertyType {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string | null;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
    properties_count?: number;
}

interface PropertyTypesShowProps {
    propertyType: PropertyType;
}

export default function PropertyTypesShow({ propertyType }: PropertyTypesShowProps) {
    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Property Types', href: '/admin/property-types' },
        { title: propertyType.name, href: `/admin/property-types/${propertyType.id}` },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Property Type: ${propertyType.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{propertyType.name}</h1>
                        <p className="text-muted-foreground">
                            Property type details and information
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" asChild>
                            <Link href="/admin/property-types">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Property Types
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={`/admin/property-types/${propertyType.id}/edit`}>
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Property Type
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                {propertyType.icon && (
                                    <span className="text-lg">{propertyType.icon}</span>
                                )}
                                <span>Basic Information</span>
                            </CardTitle>
                            <CardDescription>
                                Core details about this property type
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Name</h3>
                                    <p className="text-lg font-semibold">{propertyType.name}</p>
                                </div>
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Slug</h3>
                                    <p className="text-sm font-mono bg-muted px-2 py-1 rounded">
                                        {propertyType.slug}
                                    </p>
                                </div>
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Status</h3>
                                    <Badge variant={propertyType.is_active ? 'default' : 'secondary'}>
                                        {propertyType.is_active ? 'Active' : 'Inactive'}
                                    </Badge>
                                </div>
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground">Sort Order</h3>
                                    <p className="text-lg font-semibold">{propertyType.sort_order}</p>
                                </div>
                            </div>

                            {propertyType.description && (
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground mb-2">Description</h3>
                                    <p className="text-sm leading-relaxed">{propertyType.description}</p>
                                </div>
                            )}

                            {propertyType.icon && (
                                <div>
                                    <h3 className="font-medium text-sm text-muted-foreground mb-2">Icon</h3>
                                    <div className="flex items-center space-x-2">
                                        <span className="text-2xl">{propertyType.icon}</span>
                                        <code className="text-sm bg-muted px-2 py-1 rounded">
                                            {propertyType.icon}
                                        </code>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Usage Statistics</CardTitle>
                            <CardDescription>
                                How this property type is being used
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 gap-4">
                                <div className="p-4 border rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="font-medium">Properties</h3>
                                            <p className="text-sm text-muted-foreground">
                                                Total properties of this type
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-2xl font-bold">
                                                {propertyType.properties_count || 0}
                                            </p>
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={`/admin/properties?property_type=${propertyType.id}`}>
                                                    <Eye className="h-3 w-3 mr-1" />
                                                    View All
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div className="p-4 border rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="font-medium">Quick Actions</h3>
                                            <p className="text-sm text-muted-foreground">
                                                Common tasks for this property type
                                            </p>
                                        </div>
                                        <div className="space-y-2">
                                            <Button variant="outline" size="sm" asChild className="w-full">
                                                <Link href={`/admin/properties/create?property_type=${propertyType.id}`}>
                                                    <Plus className="h-3 w-3 mr-1" />
                                                    Add Property
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" asChild className="w-full">
                                                <Link href={`/admin/property-types/${propertyType.id}/edit`}>
                                                    <Edit className="h-3 w-3 mr-1" />
                                                    Edit Type
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Timeline</CardTitle>
                            <CardDescription>
                                Property type creation and modification history
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="flex items-start space-x-3">
                                    <div className="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <div>
                                        <h3 className="font-medium">Property Type Created</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Created on {new Date(propertyType.created_at).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                        </p>
                                    </div>
                                </div>

                                {propertyType.updated_at !== propertyType.created_at && (
                                    <div className="flex items-start space-x-3">
                                        <div className="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <div>
                                            <h3 className="font-medium">Last Updated</h3>
                                            <p className="text-sm text-muted-foreground">
                                                Modified on {new Date(propertyType.updated_at).toLocaleDateString('en-US', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
