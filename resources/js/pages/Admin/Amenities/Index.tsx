import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, Plus, Search, Star, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface AmenitiesIndexProps {
    amenities: {
        data: Array<{
            id: number;
            name: string;
            slug: string;
            description: string | null;
            category: string;
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
    categories: string[];
    filters: {
        search: string;
        category: string;
    };
}

export default function AmenitiesIndex({ amenities, categories, filters }: AmenitiesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedCategory, setSelectedCategory] = useState(filters.category || 'all');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Amenities', href: '/admin/amenities' },
    ];

    const handleSearch = () => {
        router.get('/admin/amenities', {
            search,
            category: selectedCategory !== 'all' ? selectedCategory : '',
        });
    };

    const handleReset = () => {
        setSearch('');
        setSelectedCategory('all');
        router.get('/admin/amenities');
    };

    const getCategoryColor = (category: string) => {
        const colors = {
            'indoor': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'outdoor': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'safety': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'community': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'entertainment': 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
            'utilities': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'parking': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            'accessibility': 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
        };
        return colors[category as keyof typeof colors] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Amenities" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Amenities</h1>
                        <p className="text-muted-foreground">
                            Manage amenities and features available for properties
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/admin/amenities/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Add Amenity
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter Amenities</CardTitle>
                        <CardDescription>
                            Search and filter amenities by category.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex space-x-2">
                            <Input
                                placeholder="Search amenities..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                className="max-w-sm"
                            />
                            <select
                                value={selectedCategory}
                                onChange={(e) => setSelectedCategory(e.target.value)}
                                className="px-3 py-2 border border-gray-300 rounded-md text-sm"
                            >
                                <option value="all">All Categories</option>
                                {categories.map((category) => (
                                    <option key={category} value={category}>
                                        {category.charAt(0).toUpperCase() + category.slice(1)}
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
                        <CardTitle>Amenities ({amenities.total})</CardTitle>
                        <CardDescription>
                            A list of all amenities in your system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Category</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Properties</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {amenities.data.length > 0 ? (
                                        amenities.data.map((amenity) => (
                                            <TableRow key={amenity.id}>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        {amenity.icon && (
                                                            <span className="text-lg">{amenity.icon}</span>
                                                        )}
                                                        <div>
                                                            <div className="font-medium">{amenity.name}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {amenity.slug}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={getCategoryColor(amenity.category)}>
                                                        {amenity.category}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="max-w-xs truncate">
                                                        {amenity.description || 'No description'}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">
                                                        {amenity.properties_count} properties
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        className={amenity.is_active
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                                        }
                                                    >
                                                        {amenity.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm text-muted-foreground">
                                                        {new Date(amenity.created_at).toLocaleDateString()}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/amenities/${amenity.id}`}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/amenities/${amenity.id}/edit`}>
                                                                <Edit className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                if (amenity.properties_count > 0) {
                                                                    alert('Cannot delete amenity with associated properties.');
                                                                    return;
                                                                }
                                                                if (confirm('Are you sure you want to delete this amenity?')) {
                                                                    router.delete(`/admin/amenities/${amenity.id}`);
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
                                            <TableCell colSpan={7} className="text-center py-8">
                                                <div className="space-y-2">
                                                    <Star className="h-8 w-8 mx-auto text-muted-foreground" />
                                                    <p className="text-muted-foreground">No amenities found.</p>
                                                    <Button asChild variant="outline">
                                                        <Link href="/admin/amenities/create">
                                                            <Plus className="h-4 w-4 mr-2" />
                                                            Add First Amenity
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
                        {amenities.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((amenities.current_page - 1) * amenities.per_page) + 1} to{' '}
                                    {Math.min(amenities.current_page * amenities.per_page, amenities.total)} of{' '}
                                    {amenities.total} amenities
                                </div>
                                <div className="flex space-x-2">
                                    {amenities.links.map((link, index) => (
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
