import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, Home, MapPin, Plus, Search, Trash2, User } from 'lucide-react';
import { useState } from 'react';

interface PropertiesIndexProps {
    properties: {
        data: Array<{
            id: number;
            title: string;
            slug: string;
            price: number;
            status: string;
            listing_type: string;
            bedrooms: number | null;
            bathrooms: number | null;
            area_sqft: number | null;
            address: string;
            city: string;
            created_at: string;
            property_type: {
                id: number;
                name: string;
            };
            location: {
                id: number;
                name: string;
            };
            agent: {
                id: number;
                name: string;
            };
            is_featured: boolean;
            is_active: boolean;
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
    propertyTypes: Array<{
        id: number;
        name: string;
    }>;
    locations: Array<{
        id: number;
        name: string;
    }>;
    filters: {
        search: string;
        status: string;
        listing_type: string;
        property_type_id: string;
        location_id: string;
        sort: string;
        direction: string;
    };
}

export default function PropertiesIndex({ properties, propertyTypes, locations, filters }: PropertiesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status || 'all');
    const [selectedListingType, setSelectedListingType] = useState(filters.listing_type || 'all');
    const [selectedPropertyType, setSelectedPropertyType] = useState(filters.property_type_id || 'all');
    const [selectedLocation, setSelectedLocation] = useState(filters.location_id || 'all');

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Properties', href: '/admin/properties' },
    ];

    const handleSearch = () => {
        router.get('/admin/properties', {
            search,
            status: selectedStatus !== 'all' ? selectedStatus : '',
            listing_type: selectedListingType !== 'all' ? selectedListingType : '',
            property_type_id: selectedPropertyType !== 'all' ? selectedPropertyType : '',
            location_id: selectedLocation !== 'all' ? selectedLocation : '',
        });
    };

    const handleReset = () => {
        setSearch('');
        setSelectedStatus('all');
        setSelectedListingType('all');
        setSelectedPropertyType('all');
        setSelectedLocation('all');
        router.get('/admin/properties');
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'available':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'sold':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            case 'rented':
                return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
            case 'off_market':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    const getListingTypeColor = (listingType: string) => {
        switch (listingType) {
            case 'sale':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            case 'rent':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
            case 'both':
                return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
        }).format(price);
    };

    const formatArea = (area: number | null) => {
        if (!area) return 'N/A';
        return `${area.toLocaleString()} sq ft`;
    };

    const formatBedsBaths = (bedrooms: number | null, bathrooms: number | null) => {
        const beds = bedrooms || 0;
        const baths = bathrooms || 0;
        return `${beds} bed${beds !== 1 ? 's' : ''}, ${baths} bath${baths !== 1 ? 's' : ''}`;
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Properties" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Properties</h1>
                        <p className="text-muted-foreground">
                            Manage your property listings and inventory
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/admin/properties/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Add Property
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter Properties</CardTitle>
                        <CardDescription>
                            Use the filters below to narrow down your property search.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                            <div className="lg:col-span-2">
                                <Input
                                    placeholder="Search properties..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                />
                            </div>

                            <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="available">Available</SelectItem>
                                    <SelectItem value="sold">Sold</SelectItem>
                                    <SelectItem value="rented">Rented</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="off_market">Off Market</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={selectedListingType} onValueChange={setSelectedListingType}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Types</SelectItem>
                                    <SelectItem value="sale">For Sale</SelectItem>
                                    <SelectItem value="rent">For Rent</SelectItem>
                                    <SelectItem value="both">Both</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={selectedPropertyType} onValueChange={setSelectedPropertyType}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Property Type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Property Types</SelectItem>
                                    {(propertyTypes || []).map((type) => (
                                        <SelectItem key={type.id} value={type.id.toString()}>
                                            {type.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedLocation} onValueChange={setSelectedLocation}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Location" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Locations</SelectItem>
                                    {(locations || []).map((location) => (
                                        <SelectItem key={location.id} value={location.id.toString()}>
                                            {location.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="flex space-x-2">
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
                        <CardTitle>Properties ({properties.total})</CardTitle>
                        <CardDescription>
                            A list of all properties in your system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Property</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Details</TableHead>
                                        <TableHead>Location</TableHead>
                                        <TableHead>Agent</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {(properties?.data || []).length > 0 ? (
                                        (properties?.data || []).map((property) => (
                                            <TableRow key={property.id}>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        <div className="flex items-center space-x-2">
                                                            <div className="font-medium">{property.title}</div>
                                                            {property.is_featured && (
                                                                <Badge variant="secondary" className="text-xs">
                                                                    Featured
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {property.address}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="font-medium">
                                                        {formatPrice(property.price)}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={getStatusColor(property.status)}>
                                                        {property.status.replace('_', ' ')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="space-y-1">
                                                        <Badge className={getListingTypeColor(property.listing_type)}>
                                                            {property.listing_type}
                                                        </Badge>
                                                        <div className="text-sm text-muted-foreground">
                                                            {property.property_type.name}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm">
                                                        <div>{formatBedsBaths(property.bedrooms, property.bathrooms)}</div>
                                                        <div className="text-muted-foreground">
                                                            {formatArea(property.area_sqft)}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-1">
                                                        <MapPin className="h-3 w-3 text-muted-foreground" />
                                                        <span className="text-sm">{property.location.name}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-1">
                                                        <User className="h-3 w-3 text-muted-foreground" />
                                                        <span className="text-sm">{property.agent.name}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="text-sm text-muted-foreground">
                                                        {new Date(property.created_at).toLocaleDateString()}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/properties/${property.id}`}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link href={`/admin/properties/${property.id}/edit`}>
                                                                <Edit className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => {
                                                                if (confirm('Are you sure you want to delete this property?')) {
                                                                    router.delete(`/admin/properties/${property.id}`);
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
                                            <TableCell colSpan={9} className="text-center py-8">
                                                <div className="space-y-2">
                                                    <Home className="h-8 w-8 mx-auto text-muted-foreground" />
                                                    <p className="text-muted-foreground">No properties found.</p>
                                                    <Button asChild variant="outline">
                                                        <Link href="/admin/properties/create">
                                                            <Plus className="h-4 w-4 mr-2" />
                                                            Add First Property
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
                        {properties?.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((properties.current_page - 1) * properties.per_page) + 1} to{' '}
                                    {Math.min(properties.current_page * properties.per_page, properties.total)} of{' '}
                                    {properties.total} properties
                                </div>
                                <div className="flex space-x-2">
                                    {(properties?.links || []).map((link, index) => (
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
