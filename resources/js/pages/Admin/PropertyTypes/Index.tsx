import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Building2, Edit, Eye, Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface PropertyTypesIndexProps {
    propertyTypes: {
        data: Array<{
            id: number;
            name: string;
            slug: string;
            description: string | null;
            icon: string | null;
            is_active: boolean;
            properties_count: number;
            created_at: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters?: {
        search?: string;
    };
}

export default function PropertyTypesIndex({ propertyTypes, filters }: PropertyTypesIndexProps) {
    const [search, setSearch] = useState(filters?.search || '');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Property Types', href: '/admin/property-types' },
    ];

    const handleSearch = () => {
        router.get('/admin/property-types', { search });
    };

    const handleReset = () => {
        setSearch('');
        router.get('/admin/property-types');
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Property Types" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Property Types</h1>
                        <p className="text-muted-foreground">
                            Manage the types of properties in your system
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/admin/property-types/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Add Property Type
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter Property Types</CardTitle>
                        <CardDescription>
                            Search for specific property types.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex space-x-2">
                            <Input
                                placeholder="Search property types..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                className="max-w-sm"
                            />
                            <Button onClick={handleSearch}>
                                <Search className="h-4 w-4 mr-2" />
                                Search
                            </Button>
                            <Button variant="outline" onClick={handleReset}>
                                Reset
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Property Types ({propertyTypes.total})</CardTitle>
                        <CardDescription>
                            A list of all property types in your system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Properties</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {propertyTypes.data.length > 0 ? (
                                        propertyTypes.data.map((propertyType) => (
                                            <TableRow key={propertyType.id}>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        {propertyType.icon && (
                                                            <span className="text-lg">{propertyType.icon}</span>
                                                        )}
                                                        <div>
                                                            <div className="font-medium">{propertyType.name}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {propertyType.slug}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="max-w-xs truncate">
                                                        {propertyType.description || 'No description'}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">
                                                        {propertyType.properties_count} properties
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        className={propertyType.is_active
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                                        }
                                                    >
                                                        {propertyType.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm text-muted-foreground">
                                                        {new Date(propertyType.created_at).toLocaleDateString()}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/property-types/${propertyType.id}`}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/property-types/${propertyType.id}/edit`}>
                                                                <Edit className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                if (propertyType.properties_count > 0) {
                                                                    alert('Cannot delete property type with associated properties.');
                                                                    return;
                                                                }
                                                                if (confirm('Are you sure you want to delete this property type?')) {
                                                                    router.delete(`/admin/property-types/${propertyType.id}`);
                                                                }
                                                            }}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center py-8">
                                                <div className="space-y-2">
                                                    <Building2 className="h-8 w-8 mx-auto text-muted-foreground" />
                                                    <p className="text-muted-foreground">No property types found.</p>
                                                    <Button asChild variant="outline">
                                                        <Link href="/admin/property-types/create">
                                                            <Plus className="h-4 w-4 mr-2" />
                                                            Add First Property Type
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {propertyTypes.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((propertyTypes.current_page - 1) * propertyTypes.per_page) + 1} to{' '}
                                    {Math.min(propertyTypes.current_page * propertyTypes.per_page, propertyTypes.total)} of{' '}
                                    {propertyTypes.total} property types
                                </div>
                                <div className="flex space-x-2">
                                    {propertyTypes.links.map((link, index) => (
                                        <Button
                                            key={index}
                                            variant={link.active ? "default" : "outline"}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url)}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
