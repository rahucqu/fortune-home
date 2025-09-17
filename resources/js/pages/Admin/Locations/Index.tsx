import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, MapPin, Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface LocationsIndexProps {
    locations: {
        data: Array<{
            id: number;
            name: string;
            slug: string;
            type: string;
            description: string | null;
            state: string;
            country: string;
            latitude: string | number | null;
            longitude: string | number | null;
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
    types: string[];
    filters?: {
        search?: string;
        type?: string;
    };
}

export default function LocationsIndex({ locations, types, filters }: LocationsIndexProps) {
    const [search, setSearch] = useState(filters?.search || '');
    const [selectedType, setSelectedType] = useState(filters?.type || 'all');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Locations', href: '/admin/locations' },
    ];

    const handleSearch = () => {
        router.get('/admin/locations', {
            search,
            type: selectedType !== 'all' ? selectedType : '',
        });
    };

    const handleReset = () => {
        setSearch('');
        setSelectedType('all');
        router.get('/admin/locations');
    };

    const getTypeColor = (type: string) => {
        switch (type) {
            case 'city':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            case 'suburb':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'district':
                return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
            case 'region':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
            case 'state':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Locations" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Locations</h1>
                        <p className="text-muted-foreground">
                            Manage geographic locations for property listings
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/admin/locations/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Add Location
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter Locations</CardTitle>
                        <CardDescription>
                            Search and filter locations by type.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex space-x-2">
                            <Input
                                placeholder="Search locations..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                className="max-w-sm"
                            />
                            <select
                                value={selectedType}
                                onChange={(e) => setSelectedType(e.target.value)}
                                className="px-3 py-2 border border-gray-300 rounded-md text-sm"
                            >
                                <option value="all">All Types</option>
                                {types.map((type) => (
                                    <option key={type} value={type}>
                                        {type.charAt(0).toUpperCase() + type.slice(1)}
                                    </option>
                                ))}
                            </select>
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
                        <CardTitle>Locations ({locations.total})</CardTitle>
                        <CardDescription>
                            A list of all locations in your system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Coordinates</TableHead>
                                        <TableHead>Properties</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {locations.data.length > 0 ? (
                                        locations.data.map((location) => (
                                            <TableRow key={location.id}>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">{location.name}</div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {location.slug}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={getTypeColor(location.type)}>
                                                        {location.type}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="max-w-xs truncate">
                                                        {location.description || 'No description'}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {location.latitude && location.longitude ? (
                                                        <div className="text-sm">
                                                            <div>{parseFloat(location.latitude.toString()).toFixed(4)}</div>
                                                            <div>{parseFloat(location.longitude.toString()).toFixed(4)}</div>
                                                        </div>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">Not set</span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">
                                                        {location.properties_count} properties
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        className={location.is_active
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                                        }
                                                    >
                                                        {location.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm text-muted-foreground">
                                                        {new Date(location.created_at).toLocaleDateString()}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/locations/${location.id}`}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/locations/${location.id}/edit`}>
                                                                <Edit className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                if (location.properties_count > 0) {
                                                                    alert('Cannot delete location with associated properties.');
                                                                    return;
                                                                }
                                                                if (confirm('Are you sure you want to delete this location?')) {
                                                                    router.delete(`/admin/locations/${location.id}`);
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
                                            <TableCell colSpan={8} className="text-center py-8">
                                                <div className="space-y-2">
                                                    <MapPin className="h-8 w-8 mx-auto text-muted-foreground" />
                                                    <p className="text-muted-foreground">No locations found.</p>
                                                    <Button asChild variant="outline">
                                                        <Link href="/admin/locations/create">
                                                            <Plus className="h-4 w-4 mr-2" />
                                                            Add First Location
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
                        {locations.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((locations.current_page - 1) * locations.per_page) + 1} to{' '}
                                    {Math.min(locations.current_page * locations.per_page, locations.total)} of{' '}
                                    {locations.total} locations
                                </div>
                                <div className="flex space-x-2">
                                    {locations.links.map((link, index) => (
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
